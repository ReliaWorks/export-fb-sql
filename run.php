<?php 
require 'vendor/autoload.php';

use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile(__DIR__.'/auth/wavelength.json');

$firebase = new Firebase('https://wavelength-d78bb.firebaseio.com', $config);

$reference = $firebase->getReference('/');

$all = $reference->getData();

$db = new SQLite3(__DIR__. '/db/data.db');

if ($db) {
    foreach ($all as $name => $data){

        switch ($name){
            case 'activities':
                insertActivitiesAffiliations($db, $name, $data);
                break;
            case 'affiliations':
                insertActivitiesAffiliations($db, $name, $data);
                break;

            case 'user_profiles':
                insertProfiles($db, $data);
                break;

        }
    }

}

function insertActivitiesAffiliations($db, $tableName, $data){

    $query = 'DROP TABLE IF EXISTS ' . $tableName;

    $db->exec($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS $tableName (
        id STRING PRIMARY KEY,
        icon STRING,
        name STRING,
        uid STRING
        )
EOD;
    $db->exec($query);

    $query = '';
    foreach ($data as $id => $fields){

        $fields['icon'] = SQLite3::escapeString($fields['icon']);
        $fields['name'] = SQLite3::escapeString($fields['name']);
        $fields['uid'] = SQLite3::escapeString($fields['uid']);

        $query.="INSERT INTO $tableName(id, icon, name, uid) VALUES('$id','{$fields['icon']}','{$fields['name']}','{$fields['uid']}'); \n";


    }

    echo $query;
    $db->exec($query);

}



function insertProfiles($db, $data){

    $query = 'DROP TABLE IF EXISTS user_profiles';
    $db->exec($query);

    $query = 'DROP TABLE IF EXISTS user_activities';
    $db->exec($query);

    $query = 'DROP TABLE IF EXISTS user_affiliations';
    $db->exec($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_profiles (
        uid STRING PRIMARY KEY,
        description STRING,
        email STRING,
        latitude STRING,
        longitude STRING,
        first_name STRING, 
        last_name STRING,
        current_city STRING,
        current_state STRING,
        current_county STRING,
        current_country STRING,
        status STRING
        )
EOD;
    $db->exec($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_activities (
        uid STRING,
        activity_id STRING
        )
EOD;
    $db->exec($query);


    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_affiliations (
        uid STRING,
        affiliation_id STRING
        )
EOD;
    $db->exec($query);

    $query = '';
    foreach ($data as $uid => $fields){

        $fields['description'] = SQLite3::escapeString($fields['description']);
        $fields['email'] = SQLite3::escapeString($fields['email']);
        $fields['first_name'] = SQLite3::escapeString($fields['first_name']);

        $fields['last_name'] = SQLite3::escapeString($fields['last_name']);
        $fields['latitude'] = SQLite3::escapeString($fields['latitude']);
        $fields['longitude'] = SQLite3::escapeString($fields['longitude']);

        $fields['current_city'] = SQLite3::escapeString($fields['current_city']);
        $fields['current_state'] = SQLite3::escapeString($fields['current_state']);
        $fields['current_county'] = SQLite3::escapeString($fields['current_county']);

        $fields['current_state'] = SQLite3::escapeString($fields['current_state']);
        $fields['status'] = SQLite3::escapeString($fields['status']);

        $query.="INSERT INTO user_profiles(uid, description, email, first_name,last_name,latitude,longitude,current_city, current_state,current_county, current_country, status) VALUES('$uid','{$fields['description']}','{$fields['email']}','{$fields['first_name']}', '{$fields['last_name']}', '{$fields['latitude']}', '{$fields['longitude']}', '{$fields['location']['city']}', '{$fields['location']['state']}', '{$fields['location']['county']}', '{$fields['location']['country']}', '{$fields['status']}'); \n";



        $sql = '';
        foreach ($fields['activities'] as $activity=>$item){

            $sql.="INSERT INTO user_activities(uid, activity_id) VALUES('$uid','{$activity}'); \n";

        }
        echo $sql;
        $db->exec($sql);

        $sql = '';
        foreach ($fields['affiliations'] as $id=>$item){

            $sql.="INSERT INTO user_affiliations(uid, affiliation_id) VALUES('$uid','{$id}'); \n";

        }
        echo $sql;
        $db->exec($sql);
    }

    echo $query;
    $db->exec($query);

}

echo 'Done';






