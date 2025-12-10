-- Database Schema untuk WebGIS Pendidikan Lampung Selatan
-- MySQL dengan Spatial Extension

CREATE DATABASE IF NOT EXISTS webgis_pendidikan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE webgis_pendidikan;

-- Tabel 1: Kecamatan (Batas Administrasi)
-- SRID 4326 = WGS84 (World Geodetic System 1984) - sesuai dengan GeoJSON CRS84
CREATE TABLE IF NOT EXISTS kecamatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kecamatan VARCHAR(100) NOT NULL,
    luas_km DECIMAL(10,2) DEFAULT 0.00,
    geometry GEOMETRY NOT NULL SRID 4326,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SPATIAL INDEX idx_geometry (geometry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 2: Kecamatan Analisis (Hasil Analisis Jumlah Sekolah)
-- SRID 4326 = WGS84 (World Geodetic System 1984) - sesuai dengan GeoJSON CRS84
CREATE TABLE IF NOT EXISTS kecamatan_analisis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kecamatan VARCHAR(100) NOT NULL,
    jumlah_sekolah INT DEFAULT 0,
    luas_km DECIMAL(10,2) DEFAULT 0.00,
    geometry GEOMETRY NOT NULL SRID 4326,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    SPATIAL INDEX idx_geometry (geometry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 3: Sekolah (Titik Digitasi)
-- SRID 4326 = WGS84 (World Geodetic System 1984) - sesuai dengan GeoJSON CRS84
CREATE TABLE IF NOT EXISTS sekolah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_sekolah VARCHAR(200) NOT NULL,
    jenjang VARCHAR(50) NOT NULL,
    fggpdk INT DEFAULT 0,
    kecamatan VARCHAR(100),
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    geometry POINT NOT NULL SRID 4326,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jenjang (jenjang),
    INDEX idx_kecamatan (kecamatan),
    SPATIAL INDEX idx_geometry (geometry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

