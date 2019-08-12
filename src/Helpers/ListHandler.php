<?php

class ListHandler {
    private static $username = 'TeachKloud';
    private static $password = 'b88b947a7102d95b89cc163ed89b7c9f-us15';
    private static $lists = [
        'T' => '638dca32bb',
        'P' => 'b2384e16ff',
    ];

    public function createUser($user, $list = 'T') {
        $curl = curl_init('https://us15.api.mailchimp.com/3.0/lists/'.self::$lists[$list].'/members');

        $json_data = [
            'email_address' => $user->user_email,
            'merge_fields' => [
                'FNAME' => $user->user_first_name,
                'LNAME' => $user->user_last_name,
            ],
            'status' => 'subscribed',
        ];

        try {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: '.self::$username.' '.self::$password,
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_data));
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

    public function changeEmail($old_email, $new_email, $list = 'T') {
        $email_hash = md5($old_email);

        $curl = curl_init('https://us15.api.mailchimp.com/3.0/lists/'.self::$lists[$list].'/members/'.$email_hash);

        $json_data = [
            'email_address' => $new_email,
        ];

        try {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: '.self::$username.' '.self::$password,
            ]);

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_data));
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

    public function setSubscribed($email, $status = 'No', $list = 'T') {
        $email_hash = md5($email);

        $curl = curl_init('https://us15.api.mailchimp.com/3.0/lists/'.self::$lists[$list].'/members/'.$email_hash);

        $json_data = [
            'merge_fields' => [
                'SUBSCRIBED' => $status,
            ],
        ];

        try {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: '.self::$username.' '.self::$password,
            ]);

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_data));
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }
}
