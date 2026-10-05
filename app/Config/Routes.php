<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// =====================================================
// API V1 ROUTES (GATEWAY)
// =====================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'cors'], static function ($routes) {
    
    // Auth Routes
    $routes->group('auth', static function ($routes) {
        $routes->post('login', 'Auth::login');
        $routes->post('register', 'Auth::register');
        $routes->post('onboarding', 'Auth::onboarding');
    });

    // UI Data Routes
    $routes->get('stats', 'UiData::stats');
    $routes->get('platform-settings', 'UiData::platformSettings');
    $routes->get('coupons', 'UiData::coupons');
    $routes->get('testimonials', 'UiData::testimonials');
    $routes->get('course-detail/(:any)', 'UiData::courseDetail/$1');

    // Courses and Categories
    $routes->resource('courses');
    $routes->get('categories/tree', 'Categories::tree');
    $routes->resource('categories');

    // Transactions
    $routes->post('checkout', 'Transactions::create');
        $routes->get('enrollments/(:segment)', 'Transactions::enrollments/$1');
        $routes->get('checkout/status/(:segment)', 'Transactions::status/$1');
    $routes->get('transactions/user', 'Transactions::userOrders');
    $routes->post('transactions/auto-expire', 'Transactions::autoExpire');
    
    // Webhook
    $routes->post('webhooks/midtrans', 'Webhooks::midtrans');
    // =====================================================
    // FASE 5: Classroom, Lesson Progress & Quiz Routes
    // =====================================================
    // Classroom
    $routes->get('classroom/(:segment)', 'Classroom::show/$1');

    // Lesson Progress
    $routes->post('lesson-progress', 'LessonProgress::create');

    // Quiz
    $routes->get('quizzes/(:segment)',                    'Quizzes::show/$1');
    $routes->post('quizzes/(:segment)/start',             'Quizzes::start/$1');
    $routes->post('quizzes/(:segment)/submit',            'Quizzes::submit/$1');
    $routes->get('quizzes/(:segment)/result/(:segment)', 'Quizzes::result/$1/$2');
});


