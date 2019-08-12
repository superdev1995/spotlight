<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */



/**
 * Strategy pattern for getting entities(schools, rooms, children, etc) associated with a plan
 */

/**
 * Interface PlanAssociation
 * @package App\Models
 */
interface PlanAssociation {
	function getPlanAssociations($plan_id, $assoc_type);
}

/**
 * Class SchoolAssociation
 * @package App\Models
 */
class SchoolAssociation extends App\Models\Model implements PlanAssociation {

	function getPlanAssociations($plan_id, $assoc_type) {
		try {
			$query = $this->db->prepare('
                select school_id as id, school_name as name from schools
                left join ' . $assoc_type . '_plan_assoc 
	                on ' . $assoc_type . '_plan_assoc.assoc_fk = schools.school_id
                where ' . $assoc_type . '_plan_assoc.' . $assoc_type . '_plan_fk = ?
            ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}

/**
 * Class RoomAssociation
 * @package App\Models
 */
class RoomAssociation extends App\Models\Model implements PlanAssociation {

	function getPlanAssociations($plan_id, $assoc_type) {
		try {
			$query = $this->db->prepare('
                select room_id as id, room_name as name from rooms
                left join ' . $assoc_type . '_plan_assoc 
	                on ' . $assoc_type . '_plan_assoc.assoc_fk = rooms.room_id
                where ' . $assoc_type . '_plan_assoc.' . $assoc_type . '_plan_fk = ?
            ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}

/**
 * Class ChildAssociation
 * @package App\Models
 */
class ChildAssociation extends App\Models\Model implements PlanAssociation {

	function getPlanAssociations($plan_id, $assoc_type) {
		try {
			$query = $this->db->prepare('
                select child_id as id, child_name as name from children
                left join ' . $assoc_type . '_plan_assoc 
	                on ' . $assoc_type . '_plan_assoc.assoc_fk = children.child_id
                where ' . $assoc_type . '_plan_assoc.' . $assoc_type . '_plan_fk = ?
            ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}

/**
 * Class AssociationGenerator
 * @package App\Models
 */
class AssociationGenerator extends App\Models\Model {

	private $plan_association;
	private $plan_id;
	private $plan_association_obj;
	private $assoc_type;

	public function __construct($app, $plan_id, $assoc_type) {
		parent::__construct($app);

		$this->plan_id = $plan_id;
		$this->assoc_type = $assoc_type;

		$this->getPlanAssociationType();
		
		$this->plan_association_obj = $this->AssociationObjectGenerator();
	}

	public function getAssociations() {
		return $this->plan_association_obj->getPlanAssociations($this->plan_id, $this->assoc_type);
	}

	private function AssociationObjectGenerator() {
		switch($this->plan_association['assoc']) {
			case 'school':
				return new SchoolAssociation($this);
				break;
			case 'room':
				return new RoomAssociation($this);
				break;
			case 'child':
				return new ChildAssociation($this);
				break;
		}
	}

	private function getPlanAssociationType() {
		try{
			$query = $this->db->prepare('
				select assoc from 
				' . $this->assoc_type . '_plans 
				where ' . $this->assoc_type . '_plan_id = ? 
				LIMIT 1');

			$query->execute([ $this->plan_id] );

			$this->plan_association = $query->fetch();
		} catch (\PDOException $e) {
			$this->logger->error("From AssociationGenerator: " . $e->getMessage());
		}
	}
}

/**
 * Class PlanAssociations
 * @package App\Models
 */
class PlanAssociations extends App\Models\Model {

	public function purgeAssociations($plan_id, $assoc_type){
		try{
			$query = $this->db->prepare('
				delete from ' . $assoc_type . '_plan_assoc 
				where ' . $assoc_type . '_plan_fk = ?
			');

			$query->execute([ $plan_id ]);
		} catch(\PDOException $e) {
			$this->logger->error("Class PlanAssociations: " . $e->getMessage());
		}
	}

}