<?php
require_once __DIR__ . '/../includes/db.php';
apply_security_headers();
header('Content-Type: application/json; charset=utf-8');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Method not allowed');
    verify_csrf();

    // Honeypot field for simple spam-bot filtering. Real users never see or fill this.
    if (trim((string)($_POST['website'] ?? '')) !== '') {
        throw new RuntimeException('Submission could not be accepted.');
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $phoneNormalized = normalize_bd_phone($phone);
    $companyName = trim($_POST['company_name'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $businessAddress = trim($_POST['business_address'] ?? '');
    $businessType = trim($_POST['business_type'] ?? '');
    $interestedBrand = sanitize_enquiry_brand($_POST['interested_brand'] ?? 'Both');
    $leadSource = sanitize_lead_source($_POST['lead_source'] ?? 'Website');
    $message = trim($_POST['message'] ?? '');
    $enquiryType = sanitize_enquiry_type($_POST['enquiry_type'] ?? 'Distributor');
    $productSlug = trim((string)($_POST['product_slug'] ?? ''));
    $productId = 0;
    $productName = '';
    $product = null;
    if ($productSlug !== '') {
        $product = get_product_by_slug($productSlug);
        if ($product) {
            $productId = (int)($product['id'] ?? 0);
            $productSlug = (string)($product['slug'] ?? $productSlug);
            $productName = (string)($product['name_en'] ?? '');
            $interestedBrand = sanitize_enquiry_brand($product['brand'] ?? $interestedBrand);
            if ($enquiryType === 'Distributor') $enquiryType = 'Product Enquiry';
        } else {
            $productSlug = '';
        }
    }
    if ($productName === '') {
        $productName = trim((string)($_POST['product_name'] ?? ''));
    }

    if ($name === '' || $phone === '' || $district === '' || $businessType === '') {
        throw new RuntimeException('Please fill all required fields.');
    }
    if (!in_array($district, district_list(), true)) throw new RuntimeException('Invalid district selected.');
    if (!in_array($businessType, ['Retailer','Wholesaler','Distributor','Super Shop','Other'], true)) throw new RuntimeException('Invalid business type selected.');
    if ($phoneNormalized === '' || strlen($phoneNormalized) < 10 || strlen($phoneNormalized) > 15) {
        throw new RuntimeException('Please enter a valid phone number.');
    }
    if (mb_strlen($name) > 120 || mb_strlen($phone) > 30 || mb_strlen($companyName) > 160 || mb_strlen($businessAddress) > 220 || mb_strlen($productSlug) > 220 || mb_strlen($productName) > 190 || mb_strlen($message) > 800) {
        throw new RuntimeException('Submitted data is too long.');
    }

    $pdo = db();
    ensure_enquiry_crm_columns();
    $duplicateCount = enquiry_duplicate_count($pdo, $phoneNormalized);

    $stmt = $pdo->prepare('INSERT INTO distributor_enquiries (name, phone, phone_normalized, company_name, district, business_address, business_type, interested_brand, product_id, product_slug, product_name, enquiry_type, message, lead_source, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $name,
        $phone,
        $phoneNormalized,
        $companyName,
        $district,
        $businessAddress,
        $businessType,
        $interestedBrand,
        $productId ?: null,
        $productSlug ?: null,
        $productName ?: null,
        $enquiryType,
        $message,
        $leadSource,
        $_SERVER['REMOTE_ADDR'] ?? '',
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);

    $lead = [
        'name' => $name,
        'phone' => $phone,
        'company_name' => $companyName,
        'interested_brand' => $interestedBrand,
        'product_name' => $productName,
        'enquiry_type' => $enquiryType,
    ];

    $responseMessage = $duplicateCount > 0
        ? 'Thank you. Your enquiry has been submitted again, and our team can see the earlier record too.'
        : ($enquiryType === 'Product Enquiry' ? 'Thank you. Your product enquiry has been submitted.' : 'Thank you. Your distributor enquiry has been submitted.');

    echo json_encode([
        'ok' => true,
        'message' => $responseMessage,
        'duplicate' => $duplicateCount > 0,
        'product' => $productName,
        'enquiry_type' => $enquiryType,
        'whatsapp_url' => whatsapp_url(app_config()['whatsapp_number'] ?? '', lead_whatsapp_message($lead)),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
