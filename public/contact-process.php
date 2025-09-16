<?php
/**
 * お問い合わせフォーム処理PHP
 * フォームから送信されたデータを処理し、確認画面に表示するためのデータ準備
 * 
 * このファイルは実際にはJavaScriptでセッションストレージを使用して処理していますが、
 * サーバーサイド処理が必要な場合のテンプレートとして提供しています。
 */

// セキュリティ設定
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// エラーレポート設定（本番環境では無効にする）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// CSRF対策のためのトークン生成
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * 入力値のサニタイズ
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * バリデーション関数
 */
function validate_form_data($data) {
    $errors = [];
    
    // 必須項目チェック
    if (empty($data['name'])) {
        $errors['name'] = 'お名前は必須です。';
    }
    
    if (empty($data['email'])) {
        $errors['email'] = 'メールアドレスは必須です。';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = '正しいメールアドレスの形式で入力してください。';
    }
    
    if (empty($data['subject'])) {
        $errors['subject'] = '件名は必須です。';
    }
    
    if (empty($data['message'])) {
        $errors['message'] = 'メッセージは必須です。';
    }
    
    // プライバシーポリシー同意チェック
    if (empty($data['privacy'])) {
        $errors['privacy'] = 'プライバシーポリシーに同意してください。';
    }
    
    return $errors;
}

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 入力データの取得とサニタイズ
        $form_data = [
            'name' => sanitize_input($_POST['name'] ?? ''),
            'email' => sanitize_input($_POST['email'] ?? ''),
            'phone' => sanitize_input($_POST['phone'] ?? ''),
            'subject' => sanitize_input($_POST['subject'] ?? ''),
            'budget' => sanitize_input($_POST['budget'] ?? ''),
            'deadline' => sanitize_input($_POST['deadline'] ?? ''),
            'message' => sanitize_input($_POST['message'] ?? ''),
            'privacy' => sanitize_input($_POST['privacy'] ?? '')
        ];
        
        // バリデーション
        $errors = validate_form_data($form_data);
        
        if (!empty($errors)) {
            // エラーがある場合はフォームにリダイレクト
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $form_data;
            header('Location: /contact?error=validation');
            exit;
        }
        
        // データをセッションに保存
        $_SESSION['contact_form_data'] = $form_data;
        $_SESSION['form_submitted_at'] = time();
        
        // 確認画面にリダイレクト
        header('Location: /contact-confirm');
        exit;
        
    } catch (Exception $e) {
        // エラーログに記録
        error_log('Contact form error: ' . $e->getMessage());
        
        // エラーページにリダイレクト
        header('Location: /contact?error=system');
        exit;
    }
} else {
    // POST以外のリクエストはフォームにリダイレクト
    header('Location: /contact');
    exit;
}
?>