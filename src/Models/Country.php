<?php

class Country extends App\Models\Model {
    public function getCountries() {
        try {
            $query = $this->db->prepare('
                select * from countries
                where country_available = "1"
                order by country_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCountriesAll() {
        try {
            $query = $this->db->prepare('
                select * from countries
                order by country_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCountrySubdivisionsAll() {
        try {
            $query = $this->db->prepare('
                select * from country_subdivisions
                order by country_subdivision_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCountriesNotAvailable() {
        try {
            $query = $this->db->prepare('
                select * from countries
                where country_available = "0"
                order by country_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
