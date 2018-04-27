<?php
class StudentRewardGateway extends Tdg {
	
	const PRIMARY_KEY = 'student_reward_id';
	
	const POINTS_TASK_PRACTICE = 1;
	const POINTS_TASK_TARGET = 2;
	const POINTS_LESSON_TARGET = 5;
	const POINTS_LESSON_REFLECTION = 3;
	const POINTS_GOAL = 3;
	
	const POINTSPERBADGE_PHASE_1 = 25;
	const POINTSPERBADGE_PHASE_2 = 50;
	const POINTSPERBADGE_PHASE_3 = 75;
	const POINTSPERBADGE_PHASE_4 = 100;
	
	const MAX_POINTS_PHASE_1 = 50;	// 2 * POINTSPERBADGE_PHASE_1
	const MAX_POINTS_PHASE_2 = 200;	// MAX_POINTS_PHASE_1 + (3 * POINTSPERBADGE_PHASE_2)
	const MAX_POINTS_PHASE_3 = 500;	// MAX_POINTS_PHASE_2 + (4 * POINTSPERBADGE_PHASE_3)
	
	static function getOrderByWhitelist() {
		return array(static::PRIMARY_KEY, 'reward_date');
	}
	
	static function getEventPoints() {
		return array(
			'task_practice'=>static::POINTS_TASK_PRACTICE,
			'task_target'=>static::POINTS_TASK_TARGET,
			'lesson_target'=>static::POINTS_LESSON_TARGET,
			'lesson_reflection'=>static::POINTS_LESSON_REFLECTION,
			'goal'=>static::POINTS_GOAL
		);
	}

	static function convertPointsToBadges($points) {
		if ($points <= static::MAX_POINTS_PHASE_1) {
			// Phase 1
			return floor($points / static::POINTSPERBADGE_PHASE_1);
		}
		elseif ($points <= static::MAX_POINTS_PHASE_2) {
			// Phase 2
			$pointsPrevPhases = static::MAX_POINTS_PHASE_1;
			$badgesPrevPhases = static::convertPointsToBadges($pointsPrevPhases);
			$pointsCurrentPhase = $points - $pointsPrevPhases;
			return $badgesPrevPhases + floor($pointsCurrentPhase / static::POINTSPERBADGE_PHASE_2);
		}
		elseif ($points <= static::MAX_POINTS_PHASE_3) {
			// Phase 3
			$pointsPrevPhases = static::MAX_POINTS_PHASE_2;
			$badgesPrevPhases = static::convertPointsToBadges($pointsPrevPhases);
			$pointsCurrentPhase = $points - $pointsPrevPhases;
			return $badgesPrevPhases + floor($pointsCurrentPhase / static::POINTSPERBADGE_PHASE_3);
		}
		else {
			// Phase 4
			$pointsPrevPhases = static::MAX_POINTS_PHASE_3;
			$badgesPrevPhases = static::convertPointsToBadges($pointsPrevPhases);
			$pointsCurrentPhase = $points - $pointsPrevPhases;
			return $badgesPrevPhases + floor($pointsCurrentPhase / static::POINTSPERBADGE_PHASE_4);
		}
	}

	static function convertBadgesToMinPoints($badges) {
		$points = 0;
		for ($i = 0; $i < $badges; $i++) {
			if ($points < static::MAX_POINTS_PHASE_1) {
				$points += static::POINTSPERBADGE_PHASE_1;
			}
			elseif ($points < static::MAX_POINTS_PHASE_2) {
				$points += static::POINTSPERBADGE_PHASE_2;
			}
			elseif ($points < static::MAX_POINTS_PHASE_3) {
				$points += static::POINTSPERBADGE_PHASE_3;
			}
			else {
				$points += static::POINTSPERBADGE_PHASE_4;
			}
		}
		return $points;
	}
	
	static function find($student_reward_id) {
		$stmt = DbLink::prepare("SELECT * FROM student_rewards WHERE student_reward_id = :student_reward_id");
		$stmt->bindParam(':student_reward_id', $student_reward_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	static function findAll($options=array()) {
		$sql = "SELECT * FROM student_rewards";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	static function findAllByStudent($uid, $options=array()) {
		$sql = "SELECT * FROM student_rewards WHERE uid = :uid";
		if (isset($options['orderby'])) {
			$orderby = static::sanitizeOrderBy($options['orderby'], static::getOrderByWhitelist());
			$sql .= " ORDER BY $orderby";
		}
		if (isset($options['limit'])) {
			$limit = static::sanitizeLimit($options['limit']);
			$sql .= " LIMIT $limit";
		}
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	static function insert($uid, $reward_points, $reward_event, $reward_date) {
		$sql = "INSERT INTO student_rewards (uid, reward_points, reward_event, reward_date)"
			. " VALUES (:uid, :reward_points, :reward_event, :reward_date)";
		$stmt = DbLink::prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':reward_points', $reward_points, PDO::PARAM_INT);
		$stmt->bindParam(':reward_event', $reward_event, PDO::PARAM_STR);
		$stmt->bindParam(':reward_date', $reward_date, PDO::PARAM_STR);
		$stmt->execute();
		return DbLink::lastInsertId();
	}
	static function insertTaskPractice($uid, $reward_date) {
		$reward_event = 'task_practice';
		$event_points = static::getEventPoints();
		return static::insert($uid, $event_points[$reward_event], $reward_event, $reward_date);
	}
	static function insertTaskTarget($uid, $reward_date) {
		$reward_event = 'task_target';
		$event_points = static::getEventPoints();
		return static::insert($uid, $event_points[$reward_event], $reward_event, $reward_date);
	}
	static function insertLessonTarget($uid, $reward_date) {
		$reward_event = 'lesson_target';
		$event_points = static::getEventPoints();
		return static::insert($uid, $event_points[$reward_event], $reward_event, $reward_date);
	}
	static function insertLessonReflection($uid, $reward_date) {
		$reward_event = 'lesson_reflection';
		$event_points = static::getEventPoints();
		return static::insert($uid, $event_points[$reward_event], $reward_event, $reward_date);
	}
	static function insertGoal($uid, $reward_date) {
		$reward_event = 'goal';
		$event_points = static::getEventPoints();
		return static::insert($uid, $event_points[$reward_event], $reward_event, $reward_date);
	}
	
	static function deleteAllRewardsOfStudent($uid) {
		$stmt = DbLink::prepare("DELETE FROM student_rewards WHERE uid = :uid");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
	}
	
}