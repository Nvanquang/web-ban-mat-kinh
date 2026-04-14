<?php
// app/controllers/admin/AdminConsultationController.php

class AdminConsultationController extends Controller {
    private ConsultationModel $consultationModel;

    public function __construct() {
        $this->requireAdmin();
        $this->consultationModel = new ConsultationModel();
    }

    public function index(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $status = $_GET['status'] ?? 'all';

        $result = $this->consultationModel->getAdminList($status, $page, 10);

        $this->render('admin/consultations/index', [
            'title' => 'Consultations',
            'currentPage' => 'consultations',
            'consultations' => $result['data'],
            'total' => $result['total'],
            'current_page' => $result['current_page'],
            'lastPage' => $result['last_page'],
            'currentStatus' => $status
        ], 'admin');
    }

    public function show(int $id): void {
        $consultation = $this->consultationModel->findById($id);
        if (!$consultation) {
            $this->redirect('/admin/consultations');
            return;
        }

        $this->render('admin/consultations/detail', [
            'title' => 'Consultation Detail',
            'currentPage' => 'consultations',
            'consultation' => $consultation
        ], 'admin');
    }

    public function reply(int $id): void {
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect("/admin/consultations/{$id}");
            return;
        }

        $consultation = $this->consultationModel->findById($id);
        if (!$consultation) {
            $this->redirect('/admin/consultations');
            return;
        }

        $reply = trim($_POST['reply'] ?? '');
        if (empty($reply)) {
            Session::flash('error', 'Reply cannot be empty.');
            $this->redirect("/admin/consultations/{$id}");
            return;
        }

        if (mb_strlen($reply) > 3000) {
            Session::flash('error', 'Reply is too long (max 3000 characters).');
            Session::setOldInput($_POST);
            $this->redirect("/admin/consultations/{$id}");
            return;
        }

        if ($this->consultationModel->reply($id, $reply)) {
            Session::flash('success', 'Reply sent and consultation marked as resolved.');
            $this->redirect('/admin/consultations');
        } else {
            Session::flash('error', 'Operation failed.');
            $this->redirect("/admin/consultations/{$id}");
        }
    }
}
