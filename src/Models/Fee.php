<?php

use \Carbon\Carbon;


class Fee extends App\Models\Model {
    
    public function create($idT,$nameT,$cityT,$postalCodeT,$phoneT,$emailT,$idC,$nameC,$streetC,$cityC,$postalCodeC,$invoiceNumber,$date,$vatRegNo,$currency,$fee,$hours,$ecce,$tec,$childCareScheme,$extras,$discount,$discountType,$total) {
        try {
            $query = $this->db->prepare('
                insert into billing (idT,nameT,cityT,postalCodeT,phoneT,emailT,idC,nameC,streetC,cityC,postalCodeC,invoiceNumber,date,vatRegNo,currency,fee,hours,ecce,tec,childCareScheme,extras,discount,discountType,total)
                values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');
            return $query->execute([ $idT,$nameT,$cityT,$postalCodeT,$phoneT,$emailT,$idC,$nameC,$streetC,$cityC,$postalCodeC,$invoiceNumber,$date,$vatRegNo,$currency,$fee,$hours,$ecce,$tec,$childCareScheme,$extras,$discount,$discountType,$total ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function update($child_id,$user_id,$date) {
        try {
            $query = $this->db->prepare('
                select date from billing
                where idC=?
                and idT=?
                and date<=?
                order by date desc
                limit 1
            ');
            $query->execute([ $child_id,$user_id,$date ]);
            $date2= $query->fetchcolumn();
            $query2 = $this->db->prepare('
                update billing set date=?
                where date=?
            ');
            return $query2->execute([ $date,$date2 ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function selectBillingPrevious($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select date from billing
                where idC=?
                and idT=?
                and date<=?
                order by date desc
                limit 1
            ');
            $query->execute([ $child_id,$user_id,$date ]);
            $date2= $query->fetchcolumn();
            $query2 = $this->db->prepare('
                select * from billing
                where idC=?
                and idT=?
                and date=?
            ');
            $query2->execute([ $child_id,$user_id,$date2 ]);
            return $query2->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function selectBillingOne($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select * from billing
                where idC=?
                and idT=?
                and date<=?
                order by date DESC
            ');
            $query->execute([ $child_id,$user_id,$date ]);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function day() {
        return Carbon::now()->format('Y-m-d');
    }
    public function setAvatar($user_id, $url) {
        try {
            $query = $this->db->prepare('
                update billing
                set url_logo = ?
                where idT = ?
            ');

            return $query->execute([ $url, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function selectAllBilling($user_id) {
        try {
            $query = $this->db->prepare('
                select distinct * from billing
                where idT=?
                order by date DESC 
            ');
            $query->execute([ $user_id ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
     public function selectBilling($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select * from billing
                where idC=?
                and idT=?
                and date=?
            ');
            $query->execute([ $child_id,$user_id,$date ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
     public function paid($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                update billing set status = "paid"
                where idC=?
                and idT=?
                and date=?
            ');
            return $query->execute([ $child_id,$user_id,$date ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
     public function show($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                update billing set read = "yes"
                where idC=?
                and idT=?
                and date=?
            ');
            return $query->execute([ $child_id,$user_id,$date ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
     public function parentEmail($child_id) {
        try {
            $query = $this->db->prepare('
                select user_id
                from child_user
                where child_id=?
            ');
            $query->execute([ $child_id ]);
            $user= $query->fetchcolumn();
                if (isset($child)){
                $query2 = ('
                    select *
                    from users
                    where user_id=?
                ');
                $query2->execute([ $user ]);
                return $query2->fetchAll(PDO::FETCH_OBJ);
            }else{
                    return ;
                }
                    
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
