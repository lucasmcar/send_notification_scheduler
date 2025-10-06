<?php

class NotificationRepository 
{
    private $pdo;

    public function __construct(PDO $pdo) 
    { 
        $this->pdo = $pdo; 
    }


    public function getAllDevices() 
    {
        $stmt = $this->pdo->query("SELECT * FROM notification_devices");
        return $stmt->fetchAll();
    }

    public function getAllTemplates() 
    {
        $stmt = $this->pdo->query("SELECT * FROM notification_template");
        return $stmt->fetchAll();
    }

    public function createBatch($templateId, $platforms = 'all') 
    {
        $stmt = $this->pdo->prepare("INSERT INTO notification_batch (template_id, platforms) VALUES (?, ?)");
        $stmt->execute([$templateId, $platforms]);
        return $this->pdo->lastInsertId();
    }

    public function createJob($deviceId, $batchId) 
    {
        $stmt = $this->pdo->prepare("INSERT INTO notification_job (id_notification_devices, id_notification_batch, status, attempts) VALUES (?, ?, 'pending', 0)");
        $stmt->execute([$deviceId, $batchId]);
        return $this->pdo->lastInsertId();
    }

    public function updateJob($jobId, $status, $attempts, $lastError = null) 
    {
        $stmt = $this->pdo->prepare("UPDATE notification_job SET status = ?, attempts = ?, last_error = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$status, $attempts, $lastError ? json_encode($lastError, JSON_UNESCAPED_UNICODE) : null, date('Y-m-d H:i:s'), $jobId]);
    }

    public function deleteDevice($deviceId) 
    {
        $stmt = $this->pdo->prepare("DELETE FROM notification_devices WHERE id_notification_devices = ?");
        $stmt->execute([$deviceId]);
    }

    public function incrementDeviceAttempts($deviceId) 
    {
        $stmt = $this->pdo->prepare("UPDATE notification_devices SET attempts = attempts + 1, updated = ? WHERE id_notification_devices = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $deviceId]);
    }

    public function removeDeviceAttempts($deviceId) 
    {
        $stmt = $this->pdo->prepare("UPDATE notification_devices SET attempts = 0, updated = ? WHERE id_notification_devices = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $deviceId]);
    }

    public function insertNotificationRecord($userId, $template, $message) 
    {
        $stmt = $this->pdo->prepare("INSERT INTO notification (user_id, template_id, title, subtitle, message, link, lido, excluido) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
        $stmt->execute([$userId, $template['id_notification_template'] ?? $template['id'], $template['title'], $template['subtitle'], $message, $template['link'] ?? null]);
        return $this->pdo->lastInsertId();
    }

    public function insertErrorLog($deviceId, $jobId, $errorJson, $attempts) 
    {
        $stmt = $this->pdo->prepare("INSERT INTO notification_error_log (id_notification_devices, id_notification_job, error_json, attempts) VALUES (?, ?, ?, ?)");
        $stmt->execute([$deviceId, $jobId, json_encode($errorJson, JSON_UNESCAPED_UNICODE), $attempts]);
    }

    // For seeders
    public function insertTemplate(array $t) 
    {
        $stmt = $this->pdo->prepare("INSERT INTO notification_template (id_name, template_name, title, subtitle, content, link, base, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$t['id_name'], $t['template_name'], $t['title'], $t['subtitle'], $t['content'], $t['link'], $t['base'], date('Y-m-d H:i:s')]);
    }

    public function insertDevice(array $d) {
        $stmt = $this->pdo->prepare("INSERT INTO notification_devices (user_id, device_info,  updated, fcm_token, attempts) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$d['user_id'], json_encode($d['device_info'], JSON_UNESCAPED_UNICODE),  date('Y-m-d H:i:s'), $d['fcm_token'], 0]);
    }
}
