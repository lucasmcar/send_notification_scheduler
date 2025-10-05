<?php
// NotificationSender.php
class NotificationSender {
    private $endpoint;
    private $logger;
    private $simulateFailureRate; // 0..1, probability of failure to simulate

    public function __construct($endpoint, Logger $logger, $simulateFailureRate = 0.25) {
        $this->endpoint = $endpoint;
        $this->logger = $logger;
        $this->simulateFailureRate = $simulateFailureRate;
    }

    /**
     * Envia a notificação via curl (simulando FCM). Retorna array com keys: success(bool), response(mixed)
     * Para integração FCM real, ajuste o header Authorization e o formato do payload.
     */
    public function send($fcmToken, $title, $body, $data = []) {
        // SIMULA ERRO aleatório para testar tentativas
        if (mt_rand() / mt_getrandmax() < $this->simulateFailureRate) {
            $err = ['code' => 'SIMULATED_ERROR', 'message' => 'Simulated network error'];
            $this->logger->error('Simulated send failure', ['token' => $fcmToken, 'error' => $err]);
            return ['success' => false, 'response' => $err];
        }

        // Monta payload
        $payload = [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body'  => $body
            ],
            'data' => $data
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            // Se for FCM real: "Authorization: key=YOUR_SERVER_KEY" ou Bearer token no FCM v1.
        ]);
        $resp = curl_exec($ch);
        $errno = curl_errno($ch);
        $errstr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            $this->logger->error('Curl error', ['errno' => $errno, 'error' => $errstr]);
            return ['success' => false, 'response' => ['code' => 'CURL_ERR', 'msg' => $errstr]];
        }

        $decoded = null;
        $decoded = json_decode($resp, true);
        // Em ambiente real você deve verificar a resposta do FCM (ex: success=1)
        $ok = ($httpCode >= 200 && $httpCode < 300);

        return ['success' => $ok, 'response' => $decoded ?: $resp];
    }
}
