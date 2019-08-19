CREATE TABLE `videos` (
  `video_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `video_url` varchar(512) NOT NULL,
  `video_mime_type` varchar(32) NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `uploaded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `video_daily_plan` (
  `daily_plan_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  PRIMARY KEY (`daily_plan_id`,`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `video_weekly_plan` (
  `weekly_plan_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  PRIMARY KEY (`weekly_plan_id`,`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `video_monthly_plan` (
  `monthly_plan_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  PRIMARY KEY (`monthly_plan_id`,`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `draft_daily_plans` ADD `video_group_url` VARCHAR(255) NULL AFTER `plan_img_url`;

ALTER TABLE `draft_monthly_plans` ADD `video_group_url` VARCHAR(255) NULL AFTER `group_url`;

ALTER TABLE `draft_weekly_plans` ADD `video_group_url` VARCHAR(255) NULL AFTER `name`;