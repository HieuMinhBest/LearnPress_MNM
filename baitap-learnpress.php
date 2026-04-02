<?php

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
 /* YÊU CẦU 1: Hiển thị thông báo "Học viên mới" (Notification Bar)*/

add_action( 'wp_footer', 'custom_course_notification_bar' );
function custom_course_notification_bar() {
    // Chỉ hiển thị trên trang chi tiết khóa học của LearnPress
    if ( is_singular( 'lp_course' ) ) {
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $message = "Chào " . esc_html( $current_user->display_name ) . ", bạn đã sẵn sàng bắt đầu bài học hôm nay chưa?";
        } else {
            $message = "Đăng nhập để lưu tiến độ học tập!";
        }
        // CSS nội tuyến cho thanh thông báo cố định ở trên cùng
        echo '<div style="position: fixed; top: 32px; left: 0; width: 100%; background-color: #4CAF50;
         color: white; text-align: center; padding: 10px; z-index: 9999; font-weight: bold; 
         box-shadow: 0 2px 5px rgba(0,0,0,0.2);">' . $message . '</div>';
        // Đẩy body xuống một chút để không bị thanh thông báo che mất header
        echo '<style>body { margin-top: 40px !important; }</style>';
    }
}
 /* YÊU CẦU 2: Viết hàm thống kê chi tiết cho từng Khóa học (Shortcode)*/

add_shortcode( 'lp_course_info', 'custom_lp_course_info_shortcode' );
function custom_lp_course_info_shortcode( $atts ) {
    // Lấy ID từ shortcode, mặc định là 0
    $atts = shortcode_atts( array(
        'id' => 0
    ), $atts );
    $course_id = intval( $atts['id'] );
    if ( ! $course_id ) {
        return '<p>Vui lòng cung cấp ID khóa học (Ví dụ: [lp_course_info id="123"]).</p>';
    }
    // Kiểm tra xem LearnPress đã được cài đặt & kích hoạt chưa
    if ( ! class_exists( 'LearnPress' ) ) {
        return '<p>Vui lòng cài đặt và kích hoạt plugin LearnPress.</p>';
    }
    // Lấy đối tượng khóa học từ LearnPress
    $course = learn_press_get_course( $course_id );
    if ( ! $course ) {
        return '<p>Không tìm thấy khóa học với ID: ' . $course_id . '</p>';
    }
    // 1. Số lượng bài học (Lessons)
    $lessons_count = count( $course->get_items( 'lp_lesson' ) );
    // 2. Tổng thời gian (Duration)
    $duration = get_post_meta( $course_id, '_lp_duration', true );
    if ( ! $duration ) $duration = 'Chưa xác định';
    // 3. Trạng thái của người dùng
    $status = 'Chưa đăng ký';
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $user = learn_press_get_user( $user_id );       
        if ( $user->has_finished_course( $course_id ) ) {
            $status = '<span style="color: green; font-weight: bold;">Đã hoàn thành</span>';
        } elseif ( $user->has_enrolled_course( $course_id ) ) {
            $status = '<span style="color: blue; font-weight: bold;">Đã đăng ký (Đang học)</span>';
        }
    }
    // Trả về HTML hiển thị (Dùng output buffering)
    ob_start();
    ?>
    <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0;">Thống kê Khóa học (ID: <?php echo $course_id; ?>)</h4>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><strong>Số lượng bài học:</strong> <?php echo $lessons_count; ?> bài</li>
            <li><strong>Thời gian dự kiến:</strong> <?php echo esc_html( $duration ); ?></li>
            <li><strong>Trạng thái của bạn:</strong> <?php echo $status; ?></li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
/* YÊU CẦU 3: Tùy biến Style (Custom CSS)*/
add_action( 'wp_head', 'custom_learnpress_button_styles' );
function custom_learnpress_button_styles() {
    // Chèn CSS đổi màu nút Enroll và Finish Course sang màu Cam (Thương hiệu riêng)
    ?>
    <style>
        /* Tùy biến nút Ghi danh (Enroll) và Hoàn thành (Finish) */
        .learn-press .lp-button,
        .learn-press button[type="submit"].lp-button,
        .lp-course-buttons .button-enroll-course,
        .lp-course-buttons .button-finish-course,
        form[name="enroll-course"] button {
            background-color: #ff6600 !important; /* Màu Cam */
            color: #ffffff !important;
            border-color: #ff6600 !important;
            border-radius: 5px !important;
            transition: all 0.3s ease;
        }
        /* Hiệu ứng khi di chuột qua (Hover) */
        .learn-press .lp-button:hover,
        .learn-press button[type="submit"].lp-button:hover,
        .lp-course-buttons .button-enroll-course:hover,
        .lp-course-buttons .button-finish-course:hover,
        form[name="enroll-course"] button:hover {
            background-color: #cc5200 !important; /* Màu Cam đậm hơn */
            border-color: #cc5200 !important;
        }
    </style>
    <?php
}