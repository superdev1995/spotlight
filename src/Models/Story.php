<?php

use \Carbon\Carbon;


class Story extends App\Models\Model
{
	public function getAll($child_id, $all = 0)
	{
		try {
			$query = $this->db->prepare('
                select *, stories.created_at as story_created_at from stories
                join children
                    on children.child_id = stories.child_id
                join users
                    on users.user_id = stories.user_id
                where stories.child_id = ?
                and (
                    stories.story_public = 1
                    ' . (($all == 1) ? 'or stories.story_public = 0' : '') . '
                )
                order by stories.created_at DESC
            ');

			$query->execute([$child_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getChildInStoryById($story_id)
	{
		try {
			$query = $this->db->prepare('
                select children.*, users.*, stories.created_at AS story_created_at from stories
                join children
                    on children.child_id = stories.child_id
                join users
                    on users.user_id = stories.user_id
                where stories.story_id = ?
            ');

			$query->execute([$story_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getOne($story_id)
	{
		try {
			$query = $this->db->prepare('
                select *, stories.created_at AS story_created_at from stories
                join children
                    on children.child_id = stories.child_id
                join users
                    on users.user_id = stories.user_id
                where stories.story_id_num = ?
            ');

			$query->execute([$story_id]);

			return $query->fetchObject();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getCount()
	{
		try {
			$query = $this->db->prepare('
                select count(*) from stories
            ');

			$query->execute();

			return $query->fetchColumn(0);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getChildCount($child_id)
	{
		try {
			$query = $this->db->prepare('
                select * from stories
                where child_id = ?
            ');

			$query->execute([$child_id]);

			return $query->rowCount();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getCategories($country_id)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_categories
                join frameworks
                     on frameworks.framework_id = framework_categories.framework_id
                where frameworks.country_id = ?
                order by framework_categories.framework_id, framework_categories.category_group, framework_categories.category_sort
            ');

			$query->execute([$country_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getCategoriesUS($country_id, $subcontry)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_categories
                join frameworks
                     on frameworks.framework_id = framework_categories.framework_id
                where frameworks.country_id = ? and frameworks.country_subdivision_id = ?
                order by framework_categories.framework_id, framework_categories.category_group, framework_categories.category_sort
            ');

			$query->execute([$country_id, $subcontry]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getFrameworks($country_id)
	{
		try {
			$query = $this->db->prepare('
                select frameworks.framework_id, frameworks.framework_name from frameworks
                where frameworks.country_id = ?
                GROUP BY framework_name,framework_id
            ');

			$query->execute([$country_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getFrameworksByNames($country_id, $frameworksNames)
	{
		try {
			$questions = str_repeat('?,', count($frameworksNames) - 1) . '?';

			$query = $this->db->prepare("
                select frameworks.framework_id, frameworks.framework_name from frameworks
                where frameworks.country_id = ? and frameworks.framework_name IN ($questions)
            ");

			$data = [$country_id];
			$data = array_merge($data, $frameworksNames);

			$query->execute($data);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getCategoriesByMonth($country_id, $months)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_categories
                join frameworks
                     on frameworks.framework_id = framework_categories.framework_id
                where frameworks.country_id = ?
                and (
                    (
                        frameworks.framework_month_min <= ?
                        and frameworks.framework_month_max >= ?
                    ) or (
                        frameworks.framework_month_min is null
                        and frameworks.framework_month_max is null
                    )
                )
                order by framework_categories.category_group, framework_categories.category_sort
            ');

			$query->execute([$country_id, $months, $months]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getCategoriesByMonthUS($country_id, $subcontry, $months)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_categories
                join frameworks
                     on frameworks.framework_id = framework_categories.framework_id
                where frameworks.country_id = ? and frameworks.country_subdivision_id = ?
                and (
                    (
                        frameworks.framework_month_min <= ?
                        and frameworks.framework_month_max >= ?
                    ) or (
                        frameworks.framework_month_min is null
                        and frameworks.framework_month_max is null
                    )
                )
                order by framework_categories.category_group, framework_categories.category_sort
            ');

			$query->execute([$country_id, $subcontry, $months, $months]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getGoals($category_id)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_goals
                where category_id = ?
                order by goal_sort
            ');

			$query->execute([$category_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getTexts($category_id)
	{
		try {
			$query = $this->db->prepare('
                select * from framework_texts
                where category_id = ?
                order by text_sort
            ');

			$query->execute([$category_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getStoryGoals($story_id)
	{
		try {
			$query = $this->db->prepare('
                select * from goal_story
                join framework_goals
                    on framework_goals.goal_id = goal_story.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where goal_story.story_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
            ');

			$query->execute([$story_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getChildGoals($child_id)
	{
		try {
			$query = $this->db->prepare('
                select * from goal_story
                join framework_goals
                    on framework_goals.goal_id = goal_story.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                join stories
                    on goal_story.story_id = stories.story_id
                where stories.child_id = ?
            ');

			$query->execute([$child_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getChildGoalsByFrameworkNameAndDate($child_id, $framework_name, $start_date = null, $end_date = null)
	{
		try {
			$sql = 'select * from goal_story
                join framework_goals
                    on framework_goals.goal_id = goal_story.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                join stories
                    on goal_story.story_id = stories.story_id
                where stories.child_id = ? and frameworks.framework_name = ?';

			$data = [$child_id, $framework_name];
			if (!empty($start_date)) {
				$sql .= ' and created_at >= ?';
				array_push($data, $start_date);
			}

			if (!empty($end_date)) {
				$sql .= ' and created_at <= ?';
				array_push($data, $end_date . ' 23:59:59');
			}

			$query = $this->db->prepare($sql);

			$query->execute($data);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getStoryTexts($story_id)
	{
		try {
			$query = $this->db->prepare('
                select * from text_story
                join framework_texts
                    on framework_texts.text_id = text_story.text_id
                join framework_categories
                    on framework_categories.category_id = framework_texts.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where text_story.story_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_texts.text_sort
            ');

			$query->execute([$story_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getMedias($story_id)
	{
		try {
			$query = $this->db->prepare('
                select * from media_story
                join medias
                    on medias.media_id = media_story.media_id
                where media_story.story_id = ?
            ');

			$query->execute([$story_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function create($user_id, $child_id, $data, $story_id = 0)
	{
		try {
			$query = $this->db->prepare('
            insert into stories (story_id, user_id, child_id, story_public, story_name, 
            story_action_1, story_action_2, story_action_3, story_action_4, story_action_5, story_action_6, 
            story_reflection_1, story_reflection_2, story_reflection_3, story_reflection_4, story_reflection_5, 
            keyword_1, keyword_2, keyword_3, keyword_4, keyword_5, created_at, updated_at)
            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

			$query->execute([
				$story_id,
				$user_id,
				$child_id,
				$data['public'],
				$data['story_name'],
				$data['story_action_1'],
				$data['story_action_2'],
				$data['story_action_3'],
				$data['story_action_4'],
				$data['story_action_5'],
				$data['story_action_6'],
				$data['story_reflection_1'],
				$data['story_reflection_2'],
				$data['story_reflection_3'],
				$data['story_reflection_4'],
				$data['story_reflection_5'],
				$data['keyword_1'],
				$data['keyword_2'],
				$data['keyword_3'],
				$data['keyword_4'],
				$data['keyword_5'],
				Carbon::now(),
				Carbon::now()
			]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateStotyId($story_id_num, $story_id)
	{
		try {
			$query = $this->db->prepare('
						update stories
						set story_id = ?
						where story_id_num = ?
				');

			return $query->execute([$story_id, $story_id_num]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function wrapGoals($goal_ids)
	{
		$idints = [];
		foreach ($goal_ids as $gid) {
			$idints[] =  (int)$gid;
		}
		$idss = implode(", ", $idints);
		$query = $this->db->prepare("select frameworks.framework_id, GROUP_CONCAT(framework_goals.goal_id) as `goal_ids` from frameworks
				join framework_categories on `framework_categories.framework_id = frameworks.framework_id
				join framework_goals on framework_goals.category_id = framework_categories.category_id
				WHERE framework_goals.goal_id in (" . $idss . ")
				GROUP BY frameworks.framework_id");
		$query->execute();
		return $query->fetchAll(PDO::FETCH_OBJ);
	}

	public function createGoal($goal_id, $story_id)
	{
		try {
			$query = $this->db->prepare('
                insert ignore into goal_story (goal_id, story_id)
                values (?, ?)
            ');

			return $query->execute([$goal_id, $story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function createText($text, $text_id, $story_id)
	{
		try {
			$query = $this->db->prepare('
                insert ignore into text_story (text_id, story_id, contents)
                values (?, ?, ?)
            ');

			return $query->execute([$text_id, $story_id, $text]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function createMedia($media_id, $story_id)
	{
		try {
			$query = $this->db->prepare('
                insert ignore into media_story (media_id, story_id)
                values (?, ?)
            ');

			return $query->execute([$media_id, $story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function setDetails($story_id, $data)
	{
		try {
			$query = $this->db->prepare('
                update stories
				set story_name = ?,
					story_public = ?,
                    story_action_1 = ?,
                    story_action_2 = ?,
                    story_action_3 = ?,
                    story_action_4 = ?,
                    story_action_5 = ?,
                    story_action_6 = ?,
                    story_reflection_1 = ?,
                    story_reflection_2 = ?,
                    story_reflection_3 = ?,
                    story_reflection_4 = ?,
                    story_reflection_5 = ?,
                    keyword_1 = ?,
                    keyword_2 = ?,
                    keyword_3 = ?,
                    keyword_4 = ?,
                    keyword_5 = ?,
                    updated_at = ?
                where story_id_num = ?
            ');

			return $query->execute([
				$data['story_name'],
				$data['public'],
				$data['story_action_1'],
				$data['story_action_2'],
				$data['story_action_3'],
				$data['story_action_4'],
				$data['story_action_5'],
				$data['story_action_6'],
				$data['story_reflection_1'],
				$data['story_reflection_2'],
				$data['story_reflection_3'],
				$data['story_reflection_4'],
				$data['story_reflection_5'],
				$data['keyword_1'],
				$data['keyword_2'],
				$data['keyword_3'],
				$data['keyword_4'],
				$data['keyword_5'],
				Carbon::now(),
				$story_id
			]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purge($story_id)
	{
		try {
			$query = $this->db->prepare('
                delete from stories
                where story_id = ?
            ');

			return $query->execute([$story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeGoals($story_id)
	{
		try {
			$query = $this->db->prepare('
                delete from goal_story
                where story_id = ?
            ');

			return $query->execute([$story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeTexts($story_id)
	{
		try {
			$query = $this->db->prepare('
                delete from text_story
                where story_id = ?
            ');

			return $query->execute([$story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeMediaStory($story_id)
	{
		try {
			$query = $this->db->prepare('
                delete from media_story
                where story_id = ?
            ');

			return $query->execute([$story_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeMedia($media_id)
	{
		try {
			$query = $this->db->prepare('
                delete from medias
                where media_id = ?
            ');

			return $query->execute([$media_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllbyDate($date1, $date2, $room_id)
	{
		try {
			$query = $this->db->prepare('
            select * from stories 
            join children on children.child_id = stories.child_id 
            where children.room_id= ?
            and stories.updated_at between ? and ?
            ');

			$query->execute([$room_id, $date1, $date2]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function createMulti($user_id, $child_ids, $data, $app)
	{
		$Timeline = new Timeline($app);
		$Media = new Media($app);
		$Story = new Story($app);

		$story_list = [];
		$first = true;
		$story_id_last = 0;
		foreach ($child_ids as $child_id) {
			if ($first) {
				$story_id = $Story->create((int)$user_id, $child_id, $data);
				$story_id_last = $story_id;
				$this->updateStotyId($story_id_last, $story_id);
				$first = false;
			} else {
				$story_id = $Story->create($user_id, $child_id, $data, $story_id_last);
			}

			try {
				$texts = $app->autocompleter->wrap_texts($data);
				$app->autocompleter->publish($user_id, $story_id);
				$frameworks_goals = $Story->wrapGoals($data['goals'] ? $data['goals'] : []);
				$app->autocompleter->trainRecommendation($user_id, $story_id, $texts, $frameworks_goals);
			} catch (\Exception $ex) { }
			$story_list[] = ['child_id' => $child_id, 'story_id' => $story_id];
		}

		$public = $data['public'] ? 1 : 0;

		foreach ($story_list as $story) {
			if ($data['goals']) {
				foreach ($data['goals'] as $v) {
					$Story->createGoal($v, $story['story_id']);
				}
			}

			if ($data['texts']) {
				foreach ($data['texts'] as $text_id => $text) {
					$Story->createText($text, $text_id, $story['story_id']);
				}
			}
			
			$Timeline->create($user_id, $story['child_id'], 'story', $story['story_id'], $public, $data['comment']);
		}

		if ($data['media']) {
			$this->logger->debug('Media files found.', ['group' => $data['media']]);

			$group = $this->uploader->getGroup($data['media']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url_full = $file->resize(1600)->getUrl();
				$url_thumbnail = $file->resize(400)->getUrl();

				$resized_full = $this->uploader->createLocalCopy($url_full);
				$resized_full->store();

				$resized_thumbnail = $this->uploader->createLocalCopy($url_thumbnail);
				$resized_thumbnail->store();

				$file->delete();

				foreach ($story_list as $story_id) {
					$this->logger->debug('Saved media.', ['story_id' => $story['story_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail->getUrl()]);

					$media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail->getUrl(), $resized_full->data['mime_type']);
					$Story->createMedia($media_id, $story['story_id']);
				}
			}
		}

		if (sizeof($story_list) > 1) {
			return ['multi' => true, 'story' => $story_list];
		}

		return ['multi' => false, 'story' => $story_list];
	}
}
