<?php
// scheduler.php
require_once 'config.php';
require_once 'DB.php';
require_once 'Logger.php';
require_once 'NotificationRepository.php';
require_once 'NotificationSender.php';

$cfg = require __DIR__ . '/config.php';
$db = new DB($cfg['db']);
$pdo = $db->pdo();
$logger = new Logger($cfg['log_file']);
$repo = new NotificationRepository($pdo);
$sender = new NotificationSender($cfg['fcm_endpoint'], $logger, 0.35); // 35% failure rate simulated

$logger->info("Scheduler started");

// Fetch templates and devices
$templates = $repo->getAllTemplates();
$devices = $repo->getAllDevices();

if (!$templates || !$devices) {
    $logger->info('No templates or devices found', ['templates' => count($templates), 'devices' => count($devices)]);
    exit;
}

// Loop devices -> templates as you specified: each device receives ALL templates
foreach ($devices as $device) {
    $deviceId = $device['id_notification_devices'];
    $userId = $device['user_id'];
    $fcmToken = $device['fcm_token'];
    $logger->info("Processing device", ['device_id' => $deviceId, 'token' => $fcmToken]);

    foreach ($templates as $template) {
        // Create a batch per template (you can group them differently)
        $batchId = $repo->createBatch($template['id_notification_template'] ?? $template['id'], 'all');

        // create job
        $jobId = $repo->createJob($deviceId, $batchId);

        $maxAttempts = 10;
        $attempts = 0;
        $sent = false;
        $lastError = null;

        while ($attempts < $maxAttempts && !$sent) {
            $attempts++;
            $logger->info("Attempt", ['job' => $jobId, 'attempt' => $attempts]);

            // Send
            $title = $template['title'];
            $body = $template['subtitle'] . ' - ' . ($template['content'] ?? '');
            $res = $sender->send($fcmToken, $title, $body, ['template' => $template['id_notification_template'] ?? $template['id'] ]);

            if ($res['success']) {
                $sent = true;
                $repo->updateJob($jobId, 'sent', $attempts, null);
                $repo->insertNotificationRecord($userId, $template, $body);
                $logger->info("Sent success", ['job' => $jobId, 'attempts' => $attempts, 'device' => $deviceId]);
                break;
            } else {
                // log error in table & file
                $lastError = $res['response'];
                $repo->insertErrorLog($deviceId, $jobId, $lastError, $attempts);
                $logger->error("Send failed", ['job' => $jobId, 'attempt' => $attempts, 'error' => $lastError]);

                // update job attempts
                $repo->updateJob($jobId, 'pending', $attempts, $lastError);

                // increment device attempts
                $repo->incrementDeviceAttempts($deviceId);

                // check device attempts total
                $stmt = $pdo->prepare("SELECT attempts FROM notification_devices WHERE id_notification_devices = ?");
                $stmt->execute([$deviceId]);
                $row = $stmt->fetch();
                $deviceAttempts = (int)($row['attempts'] ?? 0);

                if ($deviceAttempts >= $maxAttempts) {
                    // delete device
                    $repo->deleteDevice($deviceId);
                    $repo->updateJob($jobId, 'deleted', $attempts, $lastError);
                    $logger->error("Device deleted after reaching max attempts", ['device' => $deviceId, 'attempts' => $deviceAttempts]);
                    break 2; // break out to next device (we deleted it)
                }

                // small sleep to avoid busy loop (configurável)
                usleep(200000); // 200ms
            }
        }

        if (!$sent && $attempts >= $maxAttempts) {
            $repo->updateJob($jobId, 'failed', $attempts, $lastError);
            $logger->error("Job failed after max attempts", ['job' => $jobId]);
        }

        // small pause between notifications to avoid bursts
        usleep(100000); // 100ms
    }
}

$logger->info("Scheduler finished");
