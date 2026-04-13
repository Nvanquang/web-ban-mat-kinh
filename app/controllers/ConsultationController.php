<?php
// app/controllers/ConsultationController.php

class ConsultationController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $customerId = (int)Session::getUser()['id'];
        $model = new ConsultationModel();
        $consultations = $model->getByCustomer($customerId);

        $this->render('consultations/index', [
            'title'         => 'Consultation & Support',
            'consultations' => $consultations,
        ]);
    }

    public function send(): void {
        $this->requireAuth();

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect('/consultations');
        }

        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '') {
            Session::flash('error', 'Question cannot be empty.');
            $this->redirect('/consultations');
        }
        if (mb_strlen($content) < 10) {
            Session::flash('error', 'Question is too short (min 10 characters).');
            $this->redirect('/consultations');
        }
        if (mb_strlen($content) > 2000) {
            Session::flash('error', 'Question is too long (max 2000 characters).');
            $this->redirect('/consultations');
        }

        $customerId = (int)Session::getUser()['id'];
        $model = new ConsultationModel();
        $id = $model->create([
            'customer_id' => $customerId,
            'content'     => $content,
        ]);

        if (!$id) {
            Session::flash('error', 'Failed to send, please try again.');
            $this->redirect('/consultations');
        }

        Session::flash('success', 'Your question has been sent! We will reply shortly.');
        $this->redirect('/consultations');
    }
}

