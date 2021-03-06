<?php
/**
 * Created by PhpStorm.
 * User: Rux
 * Date: 11/01/2558
 * Time: 10:03 น.
 */

class Favorite
{
    private $wpdb;
    public $tableFavoriteJob = "ics_favorite_job";
    public $tableFavoriteCompany = "ics_favorite_company";
    public $tableCompany = "ics_company_information_for_contact";

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function listFavJob($id = 0, $job_id = 0, $user_id = 0)
    {
        $strAnd = $id ? " AND id=$id": "";
        $strAnd .= $job_id ? " AND job_id=$job_id": "";
        $strAnd .= $user_id ? " AND user_id=$user_id": "";
        $sql = "
            SELECT
              *
            FROM
              $this->tableFavoriteJob
            WHERE 1
            AND publish = 1
            $strAnd
        ";
        $result = $this->wpdb->get_results($sql);
        return $result;
    }

    public function listFavEmployer($id = 0, $employer_id = 0, $user_id = 0)
    {
        $strAnd = $id ? " AND a.id=$id": "";
        $strAnd .= $employer_id ? " AND a.employer_id=$employer_id": "";
        $strAnd .= $user_id ? " AND a.user_id=$user_id": "";
        $sql = "
            SELECT
              a.*,
              b.*,
              a.create_datetime AS fav_time
            FROM
              $this->tableFavoriteCompany a
              INNER JOIN $this->tableCompany b ON
              (a.employer_id = b.id)
            WHERE 1
            AND a.publish = 1
            $strAnd
        ";
        $result = $this->wpdb->get_results($sql);
        return $result;
    }

    public function addFavJob($user_id, $job_id, $employer_id) {
        if (!$user_id || !$job_id)
            return $this->returnMessage('Fail No id.', true);
        $sql = "
            INSERT INTO `$this->tableFavoriteJob`
            (
             `user_id`,
             `job_id`,
             `employer_id`,
             `create_datetime`,
             `update_datetime`,
             `publish`)
            VALUES (
            '{$user_id}',
            '{$job_id}',
            '{$employer_id}',
            NOW(),
            NOW(),
            1
        );
        ";
        $result = $this->wpdb->query($sql);
        if (!$result)
            $this->returnMessage('Favorite Fail.', true);
//        return $this->wpdb->insert_id;
        return $this->returnMessage('Favorite Success.', false);
    }

    public function addFavCompany($user_id, $employer_id) {
        if (!$user_id || !$employer_id)
            return $this->returnMessage('Fail No id.', true);
        $sql = "
            INSERT INTO `$this->tableFavoriteCompany`
            (
             `user_id`,
             `employer_id`,
             `create_datetime`,
             `update_datetime`,
             `publish`)
            VALUES (
            '{$user_id}',
            '{$employer_id}',
            NOW(),
            NOW(),
            1
        );
        ";
        $result = $this->wpdb->query($sql);
        if (!$result)
            return false;
//        return $this->wpdb->insert_id;
        return $this->returnMessage('Favorite Success.', false);
    }

    public function setPublishJob($id)
    {
        $sql = "
            UPDATE `$this->tableFavoriteJob`
            SET
              `publish` = 0,
              `update_datetime` = NOW()
            WHERE `id` = '{$id}';
        ";
        $result = $this->wpdb->query($sql);
        if (!$result)
            return $this->returnMessage('Sorry Edit Error.', true);
        return $this->returnMessage('Edit Success.', false);
    }

    public function setPublishCompany($id)
    {
        $sql = "
            UPDATE `$this->tableFavoriteCompany`
            SET
              `publish` = 0,
              `update_datetime` = NOW()
            WHERE `id` = '{$id}';
        ";
        $result = $this->wpdb->query($sql);
        if (!$result)
            return $this->returnMessage('Sorry Edit Error.', true);
        return $this->returnMessage('Edit Success.', false);
    }

    public function checkJobIsFavorite($user_id, $job_id) {
        $result = $this->listFavJob(0, $job_id, $user_id);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function checkCompanyIsFavorite($user_id, $employer_id) {
        $result = $this->listFavEmployer(0, $employer_id, $user_id);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
    private function returnMessage($msg, $error) {
        if ($error) {
            return json_encode(array('msg' => '<div class="font-color-BF2026"><p>'.$msg.'</p></div>', 'error' => $error));
        } else {
            return json_encode(array('msg' => '<div class="font-color-4BB748"><p>' .$msg. '</p></div>', 'error' => $error));
        }        
    }
}