<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($page_description) ? sanitize($page_description) : 'Find your dream home with Sarvam Real Estate. Premium properties in India.' ?>">
    <meta name="keywords" content="<?= isset($page_keywords) ? sanitize($page_keywords) : 'real estate, buy property, rent property, india real estate, apartments, villas' ?>">
    <title><?= isset($page_title) ? sanitize($page_title) . ' | ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($page_title) ? sanitize($page_title) . ' | ' . SITE_NAME : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($page_description) ? sanitize($page_description) : 'Find your dream home with Sarvam Real Estate.' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>">
    
    <!-- Google Fonts: Poppins & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>html{scroll-behavior:smooth;}</style>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
