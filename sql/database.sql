CREATE DATABASE IF NOT EXISTS eyeglass_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE eyeglass_db;

CREATE TABLE khach_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    ho_ten VARCHAR(100),
    sdt VARCHAR(15),
    dia_chi TEXT,
    vai_tro ENUM('khach','admin') DEFAULT 'khach',
    trang_thai ENUM('active','banned') DEFAULT 'active',
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loai_kinh (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_loai VARCHAR(50) NOT NULL,
    mieu_ta TEXT,
    trang_thai TINYINT(1) DEFAULT 1 -- 1: hiển thị, 0: ẩn
);

CREATE TABLE san_pham (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loai_kinh_id INT,
    ten_sp VARCHAR(100) NOT NULL,
    gia DECIMAL(10,2) NOT NULL,
    gia_cu DECIMAL(10,2) DEFAULT NULL, -- Giá cũ để hiển thị khuyến mãi
    so_luong INT DEFAULT 0,            -- Số lượng tồn kho
    mo_ta TEXT,
    hinh_anh VARCHAR(255),
    luot_xem INT DEFAULT 0,            -- Lượt xem để lọc sản phẩm hot
    is_custom TINYINT(1) DEFAULT 0,
    trang_thai TINYINT(1) DEFAULT 1,   -- 1: Đang bán, 0: Ngừng kinh doanh
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loai_kinh_id) REFERENCES loai_kinh(id) ON DELETE SET NULL
);

CREATE TABLE don_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    khach_hang_id INT,
    ten_nguoi_nhan VARCHAR(100),       -- Tên người nhận (phòng khi mua tặng/giao địa chỉ khác)
    sdt_nguoi_nhan VARCHAR(15),        -- SĐT người nhận
    dia_chi_giao_hang TEXT,            -- Địa chỉ giao cụ thể cho đơn này
    ghi_chu TEXT,                      -- Ghi chú của khách hàng khi đặt
    phuong_thuc_thanh_toan VARCHAR(50) DEFAULT 'COD', -- COD, Chuyển khoản, VNPay...
    ngay_dat DATETIME DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('pending','confirmed','shipped','completed','cancelled') DEFAULT 'pending',
    tong_tien DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE CASCADE
);

CREATE TABLE chi_tiet_don_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    don_hang_id INT,
    san_pham_id INT,
    so_luong INT NOT NULL,
    gia_ban DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
    FOREIGN KEY (san_pham_id) REFERENCES san_pham(id) ON DELETE SET NULL
);

CREATE TABLE tu_van (
    id INT PRIMARY KEY AUTO_INCREMENT,
    khach_hang_id INT,
    noi_dung TEXT NOT NULL,
    phan_hoi TEXT,                     -- Câu trả lời từ admin
    trang_thai ENUM('pending','resolved') DEFAULT 'pending',
    ngay_gui DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE CASCADE
);