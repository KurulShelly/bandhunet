-- ============================================================
-- BandhuNet — Migrasi tabel tracking untuk fitur Profiling
-- Jalankan di phpMyAdmin atau MySQL CLI
-- ============================================================

-- Jika tabel tracking BELUM ADA, buat dari awal:
CREATE TABLE IF NOT EXISTS `tracking` (
    `id_tracking`      INT          NOT NULL AUTO_INCREMENT,
    `id_alumni`        INT          NOT NULL,
    `status_tracking`  VARCHAR(30)  NOT NULL DEFAULT 'Not Found'
                           COMMENT 'Found / Partial / Not Found',

    -- ── Sosial media pribadi ──────────────────────────────────
    `linkedin_url`     VARCHAR(255) DEFAULT NULL,
    `instagram_url`    VARCHAR(255) DEFAULT NULL,
    `facebook_url`     VARCHAR(255) DEFAULT NULL,
    `tiktok_url`       VARCHAR(255) DEFAULT NULL,

    -- ── Kontak ───────────────────────────────────────────────
    `email`            VARCHAR(120) DEFAULT NULL,
    `no_hp`            VARCHAR(25)  DEFAULT NULL,

    -- ── Karir / Pekerjaan ────────────────────────────────────
    `tempat_kerja`     VARCHAR(255) DEFAULT NULL
                           COMMENT 'Nama perusahaan / instansi',
    `alamat_kerja`     TEXT         DEFAULT NULL,
    `posisi`           VARCHAR(150) DEFAULT NULL
                           COMMENT 'Jabatan / posisi saat ini',
    `sektor`           VARCHAR(50)  DEFAULT NULL
                           COMMENT 'PNS/ASN | Swasta | Wirausaha',
    `sosmed_kantor`    VARCHAR(255) DEFAULT NULL
                           COMMENT 'URL sosmed resmi tempat kerja',

    -- ── Data PDDIKTI ─────────────────────────────────────────
    `nama_pt_pddikti`  VARCHAR(255) DEFAULT NULL,
    `prodi_pt_pddikti` VARCHAR(150) DEFAULT NULL,
    `status_mhs`       VARCHAR(80)  DEFAULT NULL,

    -- ── Meta ─────────────────────────────────────────────────
    `sumber_data`      VARCHAR(150) DEFAULT NULL
                           COMMENT 'Sumber yang berhasil: PDDIKTI,LinkedIn,…',
    `updated_at`       DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id_tracking`),
    UNIQUE KEY  `uq_id_alumni` (`id_alumni`),
    CONSTRAINT  `fk_tracking_alumni`
        FOREIGN KEY (`id_alumni`) REFERENCES `alumni` (`id_alumni`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Jika tabel tracking SUDAH ADA, tambahkan kolom yang belum ada
-- (Jalankan satu per satu dan abaikan error "Duplicate column")
-- ============================================================

ALTER TABLE `tracking`
    ADD COLUMN IF NOT EXISTS `linkedin_url`     VARCHAR(255) DEFAULT NULL  AFTER `status_tracking`,
    ADD COLUMN IF NOT EXISTS `instagram_url`    VARCHAR(255) DEFAULT NULL  AFTER `linkedin_url`,
    ADD COLUMN IF NOT EXISTS `facebook_url`     VARCHAR(255) DEFAULT NULL  AFTER `instagram_url`,
    ADD COLUMN IF NOT EXISTS `tiktok_url`       VARCHAR(255) DEFAULT NULL  AFTER `facebook_url`,
    ADD COLUMN IF NOT EXISTS `email`            VARCHAR(120) DEFAULT NULL  AFTER `tiktok_url`,
    ADD COLUMN IF NOT EXISTS `no_hp`            VARCHAR(25)  DEFAULT NULL  AFTER `email`,
    ADD COLUMN IF NOT EXISTS `alamat_kerja`     TEXT         DEFAULT NULL  AFTER `tempat_kerja`,
    ADD COLUMN IF NOT EXISTS `posisi`           VARCHAR(150) DEFAULT NULL  AFTER `alamat_kerja`,
    ADD COLUMN IF NOT EXISTS `sektor`           VARCHAR(50)  DEFAULT NULL  AFTER `posisi`,
    ADD COLUMN IF NOT EXISTS `sosmed_kantor`    VARCHAR(255) DEFAULT NULL  AFTER `sektor`,
    ADD COLUMN IF NOT EXISTS `nama_pt_pddikti`  VARCHAR(255) DEFAULT NULL  AFTER `sosmed_kantor`,
    ADD COLUMN IF NOT EXISTS `prodi_pt_pddikti` VARCHAR(150) DEFAULT NULL  AFTER `nama_pt_pddikti`,
    ADD COLUMN IF NOT EXISTS `status_mhs`       VARCHAR(80)  DEFAULT NULL  AFTER `prodi_pt_pddikti`,
    ADD COLUMN IF NOT EXISTS `sumber_data`      VARCHAR(150) DEFAULT NULL  AFTER `status_mhs`;


-- ============================================================
-- Index untuk mempercepat filter
-- ============================================================
CREATE INDEX IF NOT EXISTS `idx_status_tracking` ON `tracking` (`status_tracking`);
CREATE INDEX IF NOT EXISTS `idx_sektor`          ON `tracking` (`sektor`);


-- ============================================================
-- Cek struktur akhir
-- ============================================================
-- DESCRIBE tracking;
