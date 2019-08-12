<?php

use \Carbon\Carbon;


class Invoice extends App\Models\Model {

    public function create($data) {
        try {
            $query = $this->db->prepare('
                insert into invoice (
                idT, nameT, cityT, postalCodeT, phoneT, emailT, idC, nameC, streetC, cityC,
                postalCodeC, invoiceNumber, `date`, `date_from`, `date_to`, open_days, week, `year` ,vatRegNo, currency, fee, hours,
                discount, discountType, discountVal, total, `status`, validate, approved , school_id, user_id, draft)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            ');

            $date_from = $data['date_from'];
            $date = new DateTime($date_from);
            $week = $date->format("W");
            $year = date('Y', strtotime($date_from));

            $query->execute([
                $data['id_user'],$data['business_name'],$data['business_city'],$data['business_PC'],$data['business_phone'],
                $data['business_email'],$data['id_child'],$data['client'],$data['client_address'],$data['client_city'],
                $data['client_PC'],$data['invoiceNumber'],$data['date'],$data['date_from'],$data['date_to'],$data['open_days'],$week,$year,$data['vatRegNo'],$data['currency'],$data['fee'],
                $data['hours'], $data['discount'],$data['discountType'],$data['discountVal'],$data['total'], $data['status'], $data['validate'], $data['approved'],
                $data['school_id'],$data['id_user'],$data['draft'] ]);
            return $this->db->lastInsertId();

            //print_r($data);
        } catch (PDOException $e)
        {
            print_r($data);
            $this->logger->error($e->getMessage());
        }
    }
    public function update($data,$id) {
        try {
            $query = $this->db->prepare
            ('  UPDATE invoice
                SET idC = ?, nameC = ?, streetC = ?, cityC = ?,
                postalCodeC = ?, invoiceNumber = ?, `date` = ?, `date_from` = ?, `date_to` = ?, open_days = ?, week = ?, `year` = ?, vatRegNo = ?, currency = ?, fee = ?, hours = ?,
                discount = ?, discountType = ?, discountVal = ?, total = ?, `status` = ?, validate = ?, approved = ?, school_id = ?, draft = ?
                WHERE  id = ? ;'
            );

            if($data['approved'] == ""){
                $data['approved'] = "no";
            }

            $date_from = $data['date_from'];
            $date = new DateTime($date_from);
            $week = $date->format("W");
            $year = date('Y', strtotime($date_from));

            return $query->execute([$data['id_child'],$data['client'],$data['client_address'],
            $data['client_city'],$data['client_PC'],$data['invoiceNumber'],$data['date'],$data['date_from'],$data['date_to'],
            $data['open_days'],$week,$year,$data['vatRegNo'],$data['currency'],$data['fee'],$data['hours'], $data['discount'],
            $data['discountType'],$data['discountVal'],$data['total'], $data['status'],$data['validate'],$data['approved_value'],$data['school_id'],$data['draft'],$id]);
            return $this->$db->lastInsertId();;
        } catch (PDOException $e) 
        {
            $this->logger->error($e->getMessage());
        }
    }
    public function invoicescheme($data,$invoice_id) {
        try {
            $query = $this->db->prepare('
            insert into invoice_scheme (
            invoice_id, ascc_type, ascc_value, ccs_type, ccs_band , ccs_value, ccsp_type, ccsp_band, ccsp_value,
            ccsr_type, ccsr_value, ccsrt_type, ccsrt_value, ccsu_type, ccsu_value, cecas_type, cecas_value, cecps_type, cecps_value,
            cets_type, cets_value, ecce_type, ecce_value)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            ');

            $query->execute([ $invoice_id,$data['ascc_type'],$data['ascc_value'],$data['ccs_type'],$data['ccs_band'],$data['ccs_value'],$data['ccsp_type'],$data['ccsp_band'],$data['ccsp_value'],
            $data['ccsr_type'],$data['ccsr_value'],$data['ccsrt_type'],$data['ccsrt_value'],$data['ccsu_type'],$data['ccsu_value'],$data['cecas_type'],$data['cecas_value'],$data['cecps_type'],$data['cecps_value'],
            $data['cets_type'],$data['cets_value'],$data['ecce'],$data['ecce_value']]);

            return $this->db->lastInsertId();


        } catch (PDOException $e) 
        {
            print_r($data);
            $this->logger->error($e->getMessage());
        }
    }


    public function insertExtras($data,$invoice_id,$child_id) {
        try {

            for($i=1;$i<=$data['extras_nb'];$i++) {

                $query = $this->db->prepare('
                insert into invoice_extras (
                invoiceNumber, child_id, extra_desc, extra_val)
                VALUES (?, ?, ?, ?);
                ');


                $desc = $data['extras_desc_' . $i . ''];
                $value = $data['extras_val_' . $i . ''];

                $query->execute([$invoice_id, $child_id, $desc, $value]);
            }

                return $this->db->lastInsertId();

        } catch (PDOException $e)
        {
            print_r($data);
            $this->logger->error($e->getMessage());
        }
    }

    public function insertExtras2($data,$invoice_id,$child_id) {
        try {

            for($i=$data['extras_nb_before']+1;$i<=5;$i++) {

                $query = $this->db->prepare('
                insert into invoice_extras (
                invoiceNumber, child_id, extra_desc, extra_val)
                VALUES (?, ?, ?, ?);
                ');


                $desc = $data['extras_desc_' . $i . ''];
                $value = $data['extras_val_' . $i . ''];

                $query->execute([$invoice_id, $child_id, $desc, $value]);
            }

                return $this->db->lastInsertId();

        } catch (PDOException $e)
        {
            print_r($data);
            $this->logger->error($e->getMessage());
        }
    }

    public function insertDuplicateInvoice($nbinvoice, $id, $child_id) {
        try {
            $query = $this->db->prepare("
            insert into invoice (idT, nameT, cityT, postalCodeT, phoneT, emailT,  idC, nameC, streetC, cityC, postalCodeC, invoiceNumber, `date`, `date_from`, `date_to`, open_days, week, `year`, vatRegNo, currency, fee, hours, discount, discountType, discountVal, total, `status`, validate, approved, url_logo, school_id, user_id)
            select idT, nameT, cityT, postalCodeT, phoneT, emailT,  idC, nameC, streetC, cityC, postalCodeC, ? , `date`, `date_from`, `date_to`, open_days, week, `year`, vatRegNo, currency, fee, hours, discount, discountType, discountVal, total, 'pending', 'no', 'no', url_logo, school_id, user_id
            from invoice
            where invoiceNumber = ?
            and idC = ?
            ");

            $query->execute([ $nbinvoice, $id, $child_id ]);

            return $this->db->lastInsertId();
        }catch (PDOException $e){
            $this->logger->error($e->getMessage());
        }
    }

     public function insertDuplicateExtras($nbinvoice, $id, $child_id) {
        try {
            $query = $this->db->prepare("
            insert into invoice_extras (invoiceNumber, child_id, extra_desc, extra_val)
            select ?, child_id, extra_desc, extra_val
            from invoice_extras
            where invoiceNumber = ?
            and child_id = ?
            ");

            $query->execute([ $nbinvoice, $id, $child_id ]);

            return $this->db->lastInsertId();
        }catch (PDOException $e){
            $this->logger->error($e->getMessage());
        }
    }

    public function getLastInvoice() {
        try {
            $query = $this->db->prepare('
                 select *
                 from invoice
                 order by id desc limit 1
            ');

            $query->execute();

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function insertDuplicateInvoiceScheme($nbinvoice, $id) {
        try {
            $query = $this->db->prepare("
            insert into invoice_scheme (invoice_id, ascc_type, ascc_value, ccs_type, ccs_band, ccs_value, ccsp_type, ccsp_band, ccsp_value, ccsr_type, ccsr_value, ccsrt_type, ccsrt_value, ccsu_type, ccsu_value, cecas_type, cecas_value, cecps_type, cecps_value, cets_type, cets_value, ecce_type, ecce_value)
            select ? , ascc_type, ascc_value, ccs_type, ccs_band, ccs_value, ccsp_type, ccsp_band, ccsp_value, ccsr_type, ccsr_value, ccsrt_type, ccsrt_value, ccsu_type, ccsu_value, cecas_type, cecas_value, cecps_type, cecps_value, cets_type, cets_value, ecce_type, ecce_value
            from invoice_scheme
            where invoice_id = ?
            ");

            $query->execute([ $nbinvoice, $id ]);

            return $this->db->lastInsertId();
        }catch (PDOException $e){
            $this->logger->error($e->getMessage());
        }
    }

    public function updateExtras($data) {
        try {

            $desc = $_POST['extras_desc'];
            $values = $_POST['extras_val'];
            $id = $_POST['extras_id'];

            foreach($_POST['extras_id'] as $key => $value){
                $query = $this->db->prepare('
                UPDATE invoice_extras
                SET extra_desc = ?, extra_val = ?
                WHERE id = ?
                ');

                  print_r($key);
                $query->execute([$desc[$key] , $values[$key], $id[$key] ]);
            }

            return $this->db->lastInsertId();

        } catch (PDOException $e)
        {
            $this->logger->error($e->getMessage());
        }
    }

    public function update_inv_scheme($data,$invoice_id) {
        try 
        {
            $query = $this->db->prepare('
            UPDATE invoice_scheme
            SET
              invoice_id = ?, ascc_type = ?, ascc_value = ?, ccs_type = ?, ccs_band = ?, ccs_value = ?, ccsp_type = ?, ccsp_band = ?, ccsp_value = ?,
              ccsr_type = ?, ccsr_value = ?, ccsrt_type = ?, ccsrt_value = ?, ccsu_type = ?, ccsu_value = ?, cecas_type = ?, cecas_value = ?, cecps_type = ?, cecps_value = ?,
              cets_type = ?, cets_value = ?, ecce_type = ?, ecce_value = ?
            WHERE
              id = ?');
            return $query->execute([ $invoice_id,$data['ascc_type'],$data['ascc_value'],$data['ccs_type'],$data['ccs_band'],$data['ccs_value'],$data['ccsp_type'],$data['ccsp_band'],$data['ccsp_value'],
            $data['ccsr_type'],$data['ccsr_value'],$data['ccsrt_type'],$data['ccsrt_value'],$data['ccsu_type'],$data['ccsu_value'],$data['cecas_type'],$data['cecas_value'],$data['cecps_type'],$data['cecps_value'],
            $data['cets_type'],$data['cets_value'],$data['ecce'],$data['ecce_value'],$invoice_id]);
        } 
        catch (PDOException $e) 
        {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectBillingPrevious($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select date from invoice
                where idC=?
                and idT=?
                and date<=?
                order by date desc
                limit 1
            ');
            $query->execute([ $child_id,$user_id,$date ]);
            $date2= $query->fetchcolumn();
            $query2 = $this->db->prepare('
                select * from invoice
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
    public function checkExistsId($child_id,$id) {
        try {
            $query = $this->db->prepare('
                select id from invoice
                where idC=?
                and invoiceNumber=?
                limit 1
            ');
            $query->execute([ $child_id,$id ]);
           return $query->fetchcolumn();
           
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectInvoiceAndScheme($child_id,$id) {
        try {
            $query = $this->db->prepare('
                select * from invoice
                inner join invoice_scheme on invoice.id = invoice_scheme.invoice_id
                where invoice.idC = ?
   				and invoice.invoiceNumber =
            ');
            $query->execute([ $child_id,$id ]);
            return $query->fetchObject();

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectBillingOne($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select * from invoice
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

    public function selectAllBilling($idT) {
        try {
            $query = $this->db->prepare('
                select * from invoice
                where idT=?

            ');
            $query->execute($idT);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectInvoiceNo($child_id) {
        try {
            $query = $this->db->prepare("
                SELECT COALESCE(MAX(invoiceNumber), 0) + 1
                  FROM invoice
                 WHERE school_id = (
                    SELECT school_id
                      FROM children
                     WHERE child_id = :child_id
                 )
            ");
            $query->execute([":child_id" => $child_id]);
            return $query->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return null;
    }

    public function day() {
        return Carbon::now()->format('Y-m-d');
    }

    public function setAvatar($user_id, $url) {
        try {
            $query = $this->db->prepare('
                update invoice
                set url_logo = ?
                where idT = ?
            ');

            return $query->execute([ $url, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectBilling($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                select * from invoice
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

    public function selectInvoice($school_id,$id) {
        try {
            $query = $this->db->prepare('
            select * from invoice as i
            inner join invoice_scheme as s on s.invoice_id = i.id
            where i.school_id=? and i.invoiceNumber=?');

            $query->execute([$school_id, $id ]);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectExtrasInvoice($id, $child_id) {
        try {
            $query = $this->db->prepare('
            select * from invoice_extras
            where invoiceNumber = ?
            and child_id = ?');

            $query->execute([ $id, $child_id ]);
            return $query->fetchAll(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


      public function selectInvoiceOne($child_id,$id) {
        try {
            $query = $this->db->prepare('
            select * from invoice as i
            where i.idC=? and i.invoiceNumber=?');
            $query->execute([$child_id, $id ]);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function selectSchemeInfo() {
        try {
            $query = $this->db->prepare('
                    SELECT  scheme_name
                    , scheme_type
                    , scheme_type_descr
                    , value_A
                    , value_AJ
                    , value_B
                    , value_D
                    FROM
                    tec_sheme_info
            ');
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function paid($user_id,$child_id,$date) {
        try {
            $query = $this->db->prepare('
                update invoice set status = "paid"
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
                update invoice set read = "yes"
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

    public function selectBillingAll($user_id,$teacher_id) {
          //var_dump($teacher_id);
        try {
            $query = $this->db->prepare('
                select * from invoice
                    inner join users on invoice.idT = users.user_id
                where idC=? and ((draft="no") or (draft="yes" and idT=?))    
            ');
            $query->execute([$user_id,$teacher_id]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }

    }

      public function selectBillingTeacher($user_id, $teacher_id) {
          //var_dump($teacher_id);
        try {
            $query = $this->db->prepare('
                select * from invoice
                where idC=?
                and idT=?
            ');
            $query->execute([ $user_id, $teacher_id ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }

      }


    public function selectBillingParent($user_id) {
          //var_dump($teacher_id);
        try {
            $query = $this->db->prepare('
                select * from invoice
                where idC=?
                and approved="yes"

            ');
            $query->execute([ $user_id ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }

      }


    public function setStatus($status, $id) {
        try {
            $query = $this->db->prepare('
                update invoice
                set status = ?
                where id = ?
            ');

            return $query->execute([ $status, $id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setValidate($validate, $id) {
        try {
            $query = $this->db->prepare('
                update invoice
                set validate = ?
                where id = ?
            ');

            return $query->execute([ $validate, $id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

}
