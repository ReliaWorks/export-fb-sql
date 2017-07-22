<?php 
require 'vendor/autoload.php';

use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile(__DIR__.'/auth/wavelength.json');

$firebase = new Firebase('https://wavelength-d78bb.firebaseio.com', $config);

$reference = $firebase->getReference('/');

$all = $reference->getData();

$connection = pg_connect("host=ec2-23-23-248-162.compute-1.amazonaws.com dbname=daaqbc1amil5l4 user=dudtmyklemtlxv password=b7ab3c9de3ee9916fa208fa6bfdb091ad0f0edc6840671f2fc60a2ef670c7aaa")
or die('Could not connect: ' . pg_last_error());

if ($connection) {
    foreach ($all as $name => $data){

        switch ($name){
            case 'activities':
                insertActivitiesAffiliations($connection,$name, $data);
                break;
            case 'affiliations':
                insertActivitiesAffiliations($connection,$name, $data);
                break;
            case 'message_center':
                insertMessageCenter($connection,$name, $data);
                break;
            case 'user_profiles':
                insertProfiles($connection,$data);
                break;

        }
    }

}

function insertActivitiesAffiliations($connection,$tableName, $data){

    $query = 'DROP TABLE IF EXISTS w_' . $tableName;
    pg_query($query);


    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS w_$tableName (
        id VARCHAR(50) PRIMARY KEY,
        icon VARCHAR(500),
        name VARCHAR(50),
        uid VARCHAR(50)
        )
EOD;
    pg_query($query);

    $query = '';
    foreach ($data as $id => $fields){

        $fields['icon'] = pg_escape_string($fields['icon']);
        $fields['name'] = pg_escape_string($fields['name']);
        $fields['uid'] = pg_escape_string($fields['uid']);

        $query.="INSERT INTO w_$tableName(id, icon, name, uid) VALUES('$id','{$fields['icon']}','{$fields['name']}','{$fields['uid']}'); \n";


    }

    echo $query;
    pg_query($query);

}

function insertMessageCenter($connection,$tableName, $data){

    $query = 'DROP TABLE IF EXISTS w_' . $tableName;
    pg_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS w_$tableName (
        uid1 VARCHAR(50),
        uid2 VARCHAR(50),
        conversation_id VARCHAR(50)
        );
EOD;
    pg_query($query);


    $query='';

    foreach ($data as $uid1 => $messages){
        foreach ($messages as $uid2 => $fields)
            if ($uid1!='undefined' && isset($fields['conversationId']) && $fields['conversationId']){
                $query.="INSERT INTO w_$tableName(uid1, uid2, conversation_id) VALUES('$uid1','$uid2','{$fields['conversationId']}'); \n";
                pg_query($query);
            }

    }

}

function insertProfiles($connection,$data){

    $query = 'DROP TABLE IF EXISTS w_user_profiles';
    pg_query($query);

    $query = 'DROP TABLE IF EXISTS w_user_activities';
    pg_query($query);

    $query = 'DROP TABLE IF EXISTS w_user_affiliations';
    pg_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS w_user_profiles (
        uid VARCHAR(50) PRIMARY KEY,
        description TEXT,
        email VARCHAR(50),
        latitude VARCHAR(50),
        longitude VARCHAR(50),
        first_name VARCHAR(50), 
        last_name VARCHAR(50),
        current_city VARCHAR(50),
        current_state VARCHAR(50),
        current_county VARCHAR(50),
        current_country VARCHAR(50),
        status VARCHAR(50)
        )
EOD;
    pg_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS w_user_activities (
        uid VARCHAR(50),
        activity_id VARCHAR(50)
        )
EOD;
    pg_query($query);


    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS w_user_affiliations (
        uid VARCHAR(50),
        affiliation_id VARCHAR(50)
        )
EOD;
    pg_query($query);

    $query = '';
    foreach ($data as $uid => $fields){

        $fields['description'] = pg_escape_string($fields['description']);
        $fields['email'] = pg_escape_string($fields['email']);
        $fields['first_name'] = pg_escape_string($fields['first_name']);

        $fields['last_name'] = pg_escape_string($fields['last_name']);
        $fields['latitude'] = pg_escape_string($fields['latitude']);
        $fields['longitude'] = pg_escape_string($fields['longitude']);

        $fields['location']['city'] = pg_escape_string($fields['location']['city']);
        $fields['location']['state'] = pg_escape_string($fields['location']['state']);
        $fields['location']['county'] = pg_escape_string($fields['location']['county']);

        $fields['location']['state'] = pg_escape_string($fields['location']['state']);
        $fields['status'] = pg_escape_string($fields['status']);

        $query.="INSERT INTO w_user_profiles(uid, description, email, first_name,last_name,latitude,longitude,current_city, current_state,current_county, current_country, status) VALUES('$uid','{$fields['description']}','{$fields['email']}','{$fields['first_name']}', '{$fields['last_name']}', '{$fields['latitude']}', '{$fields['longitude']}', '{$fields['location']['city']}', '{$fields['location']['state']}', '{$fields['location']['county']}', '{$fields['location']['country']}', '{$fields['status']}'); \n";

        $fields['activities'] = $fields['activities']?:array();

        $sql = '';

        foreach ($fields['activities'] as $activity=>$item){

            $sql ="INSERT INTO w_user_activities(uid, activity_id) VALUES('$uid','{$activity}'); \n";
            pg_query($sql);

        }



        $sql = '';

        $fields['affiliations'] = $fields['affiliations']?:array();

        foreach ($fields['affiliations'] as $id=>$item){

            $sql ="INSERT INTO w_user_affiliations(uid, affiliation_id) VALUES('$uid','{$id}'); ";
            pg_query($sql);

        }

    }

    echo $query;
    pg_query($query);

}

echo 'Done';






