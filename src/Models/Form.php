<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

class Form extends App\Models\Model {

	public function retrieveBlocks($plan_name, $school_country) {

		try{
			$query = $this->db->prepare('
                select * from form
                left join form_country on form_country.form_fk = form.form_id
                left join form_blocks on form_country.form_country_id = form_blocks.form_country_fk
                where form_name = ? AND form_country.country_fk = ?
            ');

			$query->execute([ $plan_name, $school_country]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	public function retrieveBlocksByCountrySubdivision($plan_name, $school_country_subdivision) {

		try{
			$query = $this->db->prepare('
                select * from form
                left join form_country on form_country.form_fk = form.form_id
                left join form_blocks on form_country.form_country_id = form_blocks.form_country_fk
                where form_name = ? AND form_country.country_subdivision_fk = ?
            ');

			$query->execute([ $plan_name, $school_country_subdivision]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}