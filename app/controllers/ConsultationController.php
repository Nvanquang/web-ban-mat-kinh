<?php
// app/controllers/ConsultationController.php

class ConsultationController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $customerId = (int)Session::getUser()['id'];
        $model = new ConsultationModel();
        $consultations = $model->getByCustomer($customerId);

        $this->render('consultations/index', [
            'title'         => 'Tư vấn & Hỗ trợ',
            'consultations' => $consultations,
        ]);
    }

    public function send(): void {
        $this->requireAuth();

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            Session::flash('error', 'Token CSRF không hợp lệ.');
            $this->redirect('/consultations');
        }

        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '') {
            Session::flash('error', 'Vui lòng nhập nội dung câu hỏi.');
            $this->redirect('/consultations');
        }
        if (mb_strlen($content) < 10) {
            Session::flash('error', 'Câu hỏi quá ngắn (tối thiểu 10 ký tự).');
            $this->redirect('/consultations');
        }
        if (mb_strlen($content) > 2000) {
            Session::flash('error', 'Câu hỏi quá dài (tối đa 2000 ký tự).');
            $this->redirect('/consultations');
        }

        $customerId = (int)Session::getUser()['id'];
        $model = new ConsultationModel();
        $id = $model->create([
            'customer_id' => $customerId,
            'content'     => $content,
        ]);

        if (!$id) {
            Session::flash('error', 'Gửi câu hỏi thất bại. Vui lòng thử lại sau.');
            $this->redirect('/consultations');
        }

        Session::flash('success', 'Câu hỏi của bạn đã được gửi thành công! Chúng tôi sẽ trả lời trong thời gian sớm nhất.');
        $this->redirect('/consultations');
    }
}