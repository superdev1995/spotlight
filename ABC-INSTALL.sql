CREATE TABLE `abc` (
  `abc_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `abc_assoc` enum('school','room','child') NOT NULL,
  `abc_date` date NOT NULL,
  `abc_time` time NOT NULL,
  `abc_antecedents` text NOT NULL,
  `abc_behaviour` text NOT NULL,
  `abc_consequence` text NOT NULL,
  `abc_comment` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `abc_plan_assoc` (
  `abc_plan_assoc_id` int(11) NOT NULL,
  `abc_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `abc`
  ADD PRIMARY KEY (`abc_id`);
  MODIFY `abc_id` int(11) AUTO_INCREMENT;

ALTER TABLE `abc_plan_assoc`
  ADD PRIMARY KEY (`abc_plan_assoc_id`);
  MODIFY `abc_plan_assoc_id` int(11) AUTO_INCREMENT;