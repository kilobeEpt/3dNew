-- Migration: Add file upload support to cost_estimates table
-- Description: Adds columns for 3D model file uploads and calculator metadata
-- Created: 2024-01-01

ALTER TABLE `cost_estimates`
ADD COLUMN `file_path` VARCHAR(500) NULL AFTER `notes`,
ADD COLUMN `file_original_name` VARCHAR(255) NULL AFTER `file_path`,
ADD COLUMN `file_size` INT UNSIGNED NULL AFTER `file_original_name`,
ADD COLUMN `file_mime_type` VARCHAR(100) NULL AFTER `file_size`,
ADD COLUMN `calculator_data` JSON NULL COMMENT 'Stores calculator-specific inputs (material, dimensions, infill, etc.)' AFTER `file_mime_type`,
ADD COLUMN `source` ENUM('manual', 'calculator', 'api') NOT NULL DEFAULT 'manual' AFTER `calculator_data`,
KEY `idx_cost_estimates_source` (`source`);
