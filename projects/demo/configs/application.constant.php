<?php
    // Permission
    defined ('APP_PERMISSION_ROLE') || define('APP_PERMISSION_ROLE', [
        'all'       => ['SUPPER_ADMIN', 'MANAGER', 'STAFF', 'SUPPORT'],
        'admin'     => ['SUPPER_ADMIN', 'MANAGER', 'SUPPORT'],
        'supper'    => ['SUPPER_ADMIN', 'SUPPORT'],
        'support'   => ['SUPPORT'],
    ]);

    // Upload
    defined ('ROOT_PUBLIC_PATH') || define('ROOT_PUBLIC_PATH', PUBLIC_PATH);
    defined ('ADMIN_IMG_PATH') || define('ADMIN_IMG_PATH', '/uploads' );

    // Message
    defined ('ZF_MSG_DATA_NOT_EXISTS') || define('ZF_MSG_DATA_NOT_EXISTS', 'Dữ liệu không tồn tại');
    defined ('ZF_MSG_UPDATE_SUCCESS') || define('ZF_MSG_UPDATE_SUCCESS', 'Cập nhật dữ liệu thành công');
    defined ('ZF_MSG_UPDATE_FAIL') || define('ZF_MSG_UPDATE_FAIL', 'Cập nhật dữ liệu thất bại');
    defined ('ZF_MSG_ADD_FAIL') || define('ZF_MSG_ADD_FAIL', 'Thêm mới dữ liệu thất bại');
    defined ('ZF_MSG_ADD_SUCCESS') || define('ZF_MSG_ADD_SUCCESS', 'Thêm mới dữ liệu thành công');
    defined ('ZF_MSG_DUPLICATE_FAIL') || define('ZF_MSG_DUPLICATE_FAIL', 'Tạo bản sao dữ liệu thất bại');
    defined ('ZF_MSG_DUPLICATE_SUCCESS') || define('ZF_MSG_DUPLICATE_SUCCESS', 'Tạo bản sao dữ liệu thành công');
    defined ('ZF_MSG_SAVE_ORDER_SUCCESS') || define('ZF_MSG_SAVE_ORDER_SUCCESS', 'Cập nhật thứ tự thành công');
    defined ('ZF_MSG_DEL_SUCCESS') || define('ZF_MSG_DEL_SUCCESS', 'Xóa dữ liệu thành công');
    defined ('ZF_MSG_DEL_FAIL') || define('ZF_MSG_DEL_FAIL', 'Xóa dữ liệu không thành công');
    defined ('ZF_MSG_NOT_EMPTY') || define('ZF_MSG_NOT_EMPTY', 'Vui lòng nhập đầy đủ thông tin');
    define('ZF_MSG_REFRESH_CACHE_SUCCESS', 'Refresh cache success');
    define('ZF_MSG_REFRESH_CACHE_FAIL', 'Refresh cache fail');

    defined ('NO_REPLY_EMAIL') || define('NO_REPLY_EMAIL', 'no-reply@demo.local');
    defined ('SIGN_UP_EMAIL') || define('SIGN_UP_EMAIL', 'sign-up@demo.local');
    
    defined ('DISPLAY_VERSION') || define('DISPLAY_VERSION', 'Ver.1');
    defined ('REDIS_CONFIG') || define('REDIS_CONFIG', require __DIR__ . '/redis.configs.php'); 
    /**
     * Facebook configs
     * @var array
     */
    defined ('FB_CONFIG') || define('FB_CONFIG', '');
    require 'custom/application.constant.php';
?>
