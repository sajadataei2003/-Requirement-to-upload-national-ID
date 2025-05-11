<?php
/*
Plugin Name: Requirement to upload national ID card and add to shopping cart
Plugin URI: https://yourdomain.com
Description: جلوگیری از افزودن به سبد خرید بدون آپلود کارت ملی در فرم Gravity Forms برای محصولات ووکامرس.
Version: 1.0
Author: sajjad ataei
*/

// مرحله 1: بررسی بارگذاری کارت ملی در Gravity Forms
add_action('wp_footer', 'sajad_check_national_id_upload', 100);
function sajad_check_national_id_upload() {
    if (!is_product()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.querySelector('#input_2_14'); // آیدی فیلد کارت ملی
        const addToCartButton = document.querySelector('.single_add_to_cart_button'); // دکمه افزودن به سبد خرید

        if (!fileInput || !addToCartButton) return;

        addToCartButton.addEventListener('click', function (e) {
            if (fileInput.files.length === 0) {
                alert('لطفاً تصویر کارت ملی را بارگذاری کنید.');
                e.preventDefault(); // جلوگیری از ارسال فرم و افزودن به سبد خرید
            } else {
                // ارسال فرم Gravity Forms و افزودن به سبد خرید ووکامرس
                const form = document.querySelector('form#gform_2');
                form.submit(); // ارسال فرم Gravity Forms
            }
        });
    });
    </script>
    <?php
}

// مرحله 2: افزودن فایل کارت ملی به سبد خرید
add_filter('woocommerce_add_cart_item_data', 'add_national_id_to_cart', 10, 3);
function add_national_id_to_cart($cart_item_data, $product_id, $quantity) {
    // بررسی فایل کارت ملی از Gravity Forms
    if (isset($_FILES['input_14']) && !empty($_FILES['input_14']['name'])) {
        // ذخیره اطلاعات فایل کارت ملی در سبد خرید
        $cart_item_data['national_id'] = $_FILES['input_14'];
    }
    return $cart_item_data;
}

// مرحله 3: نمایش فایل کارت ملی در سبد خرید
add_filter('woocommerce_get_item_data', 'display_national_id_in_cart', 10, 2);
function display_national_id_in_cart($item_data, $cart_item) {
    if (isset($cart_item['national_id'])) {
        $item_data[] = array(
            'key'   => 'کارت ملی',
            'value' => $cart_item['national_id']['name'] // نمایش نام فایل کارت ملی در سبد خرید
        );
    }
    return $item_data;
}

// مرحله 4: ذخیره فایل کارت ملی در فاکتور
add_action('woocommerce_checkout_create_order_line_item', 'add_national_id_to_order', 10, 4);
function add_national_id_to_order($item, $cart_item_key, $values, $order) {
    if (isset($values['national_id'])) {
        // اضافه کردن کارت ملی به سفارش
        $item->add_meta_data('کارت ملی', $values['national_id']['name'], true);
    }
}

// مرحله 5: بررسی فایل کارت ملی قبل از افزودن به سبد خرید
add_filter('woocommerce_add_to_cart_validation', 'sajad_validate_national_id_upload', 10, 3);
function sajad_validate_national_id_upload($passed, $product_id, $quantity) {
    if (is_admin()) return $passed;

    // بررسی فایل کارت ملی از Gravity
    if (isset($_FILES['input_14']) && empty($_FILES['input_14']['name'])) {
        wc_add_notice('آپلود کارت ملی الزامی است.', 'error');
        return false;
    }

    return $passed;
}
?>
