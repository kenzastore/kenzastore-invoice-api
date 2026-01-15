<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class RecurringController {
    private $db;
    private $userId;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function processRequest($method, $id) {
        $user = AuthMiddleware::authenticate();
        $this->userId = $user['user_id'];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getSchedule($id);
                } else {
                    $this->listSchedules();
                }
                break;
            case 'POST':
                $this->createSchedule();
                break;
            case 'PUT':
                $this->updateSchedule($id);
                break;
            case 'DELETE':
                $this->deleteSchedule($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
                break;
        }
    }

    private function listSchedules() {
        $stmt = $this->db->prepare("SELECT * FROM recurring_invoices WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $schedules]);
    }

    private function getSchedule($id) {
        $stmt = $this->db->prepare("SELECT * FROM recurring_invoices WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Schedule not found']);
            return;
        }
        echo json_encode(['status' => 'success', 'data' => $schedule]);
    }

    private function createSchedule() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['invoice_template_id'], $data['frequency'], $data['start_date'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        // Verify template belongs to user
        $stmt = $this->db->prepare("SELECT id FROM invoices WHERE id = ? AND user_id = ?");
        $stmt->execute([$data['invoice_template_id'], $this->userId]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid template ID']);
            return;
        }

        $nextRunDate = $this->calculateNextRun($data['start_date'], $data['frequency']);

        $sql = "INSERT INTO recurring_invoices (user_id, invoice_template_id, frequency, start_date, next_run_date, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $status = $data['status'] ?? 'active';
        $stmt->execute([
            $this->userId, 
            $data['invoice_template_id'], 
            $data['frequency'], 
            $data['start_date'], 
            $nextRunDate,
            $status
        ]);

        $id = $this->db->lastInsertId();
        echo json_encode(['status' => 'success', 'data' => ['id' => $id]]);
    }

    private function updateSchedule($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID required']);
            return;
        }

        $fields = [];
        $params = [];
        if (isset($data['frequency'])) {
            $fields[] = "frequency = ?";
            $params[] = $data['frequency'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        if (isset($data['next_run_date'])) {
            $fields[] = "next_run_date = ?";
            $params[] = $data['next_run_date'];
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            return;
        }

        $params[] = $id;
        $params[] = $this->userId;
        $sql = "UPDATE recurring_invoices SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Schedule updated']);
    }

    private function deleteSchedule($id) {
        $stmt = $this->db->prepare("DELETE FROM recurring_invoices WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        echo json_encode(['status' => 'success', 'message' => 'Schedule deleted']);
    }

    private function calculateNextRun($startDate, $frequency) {
        $date = new DateTime($startDate);
        switch ($frequency) {
            case 'weekly':
                $date->modify('+1 week');
                break;
            case 'monthly':
                $date->modify('+1 month');
                break;
            case 'annually':
                $date->modify('+1 year');
                break;
        }
        return $date->format('Y-m-d');
    }
}
