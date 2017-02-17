<?php

$__mysqliHandler = mysqli_connect('localhost', 'root', '', 'test');


$params = json_decode(file_get_contents('php://input'), true);


$object = $params['object'] ?? 'tables';
$action = $params['action'] ?? 'select';

//header('Content-type: application/json');
header('Content-type: text/html; charset=utf-8');

switch ($object)
{
    case 'db':
        switch ($action)
        {
            case 'getTables':
                if ($result = $__mysqliHandler->query('show tables'))
                {
                    $out = array();
                    if ($result->num_rows > 0)
                    {
                        $out = array('code' => 'ok.');
                        $data = array();
                        while ($row = $result->fetch_row())
                            array_push($data, $row[0]);
                        $out['data'] = $data;
                        echo json_encode($out);
                    }
                    else
                        echo('{"code": "no table"}');
                }
                else
                    echo('{"code": "no db"}');
                break;
            default:
                echo('{"code": "Not selected action"}');

        }
        break;

    case 'table':
        $tableName = cut($__mysqliHandler, $params['table']);
        if (empty($tableName))
        {
            echo('{"code": "Wrong table name."}');
            return;
        }
        switch ($action)
        {
            case 'getColumns':
                if ($result = $__mysqliHandler->query('SHOW COLUMNS FROM ' . $tableName))
                {
                    $out = array('code' => 'ok.');
                    $data = array();
                    while ($row = $result->fetch_row())
                    {
                        if ($row[3] != 'PRI')
                        array_push($data, $row[0]);
                    }
                    $out['data'] = $data;
                    echo json_encode($out);
                }
                else
                    echo('{"code": "Wrong table name."}');
                break;
            case 'selectRecords':

                $out = array();
                if ($result = $__mysqliHandler->query('select * from ' .$tableName. ' limit 5'))
                {
                    if ($result->num_rows > 0)
                    {
                        $out = array('code' => 'ok.');
                        $data = array();
                        while ($row = $result->fetch_assoc())
                            array_push($data, $row);
                        $out['data'] = $data;
                        echo json_encode($out);
                    }
                    else
                        echo('{"code": "no record"}');
                }
                else
                    echo('{"code": "Wrong table name."}');

                break;
            case 'insertRecord':

                if (empty($params['columnData']))
                {
                    echo('{"code": "Wrong columns data."}');
                    return;
                }

                $query = 'INSERT INTO ' . $tableName . '(' . implode(',', array_keys($params['columnData'])) . ') '.
                    'VALUES("' . implode('","', array_values($params['columnData'])) . '")';
//                print_r($params);
//                print_r($query);

                if ($__mysqliHandler->query($query))
                    echo('{"code": "Data was inserted."}');
                else
                    echo('{"code": "Error insert."}');

                break;

            case 'getRecord':

                if (intval($params['recordId']) == 0)
                {
                    echo('{"code": "Wrong record id."}');
                    return;
                }

                $out = array();
                if ($result = $__mysqliHandler->query('select * from ' .$tableName. ' WHERE id=' . intval($params['recordId'])))
                {
                    if ($result->num_rows > 0)
                    {
                        $out = array('code' => 'ok.');
                        $out['data'] = $result->fetch_assoc();
                        $primKeyName = '';
                        if ($result = $__mysqliHandler->query('SHOW COLUMNS FROM ' . $tableName))
                            while ($row = $result->fetch_row())
                                if ($row[3] == 'PRI')
                                    $primKeyName = $row[0];
                        foreach ($out['data'] as $k=>$v)
                            if ($k == $primKeyName)
                                unset($out['data'][$k]);
                        echo json_encode($out);
                    }
                    else
                        echo('{"code": "no record"}');
                }
                else
                    echo('{"code": "Wrong table name."}');


                break;

            case 'updateRecord':

                if (intval($params['recordId']) == 0)
                {
                    echo('{"code": "Wrong record id."}');
                    return;
                }

                if (empty($params['columnDataList']))
                {
                    echo('{"code": "Wrong columns data."}');
                    return;
                }
                $columnDataListStr = '';

                foreach ($params['columnDataList'] as $k=>$v)
                    $columnDataListStr .= cut($__mysqliHandler, $k).'="'.cut($__mysqliHandler, $v).'", ';


                $query = 'UPDATE ' . $tableName . ' SET ' .  substr($columnDataListStr, 0, strlen ($columnDataListStr)-2) . ' WHERE id = ' . intval($params['recordId']);

                if ($__mysqliHandler->query($query))
                    echo('{"code": "Data was updated."}');
                else
                    echo('{"code": "Error update."}');

                break;

            case 'deleteRecord':

                if (intval($params['recordId']) == 0)
                {
                    echo('{"code": "Wrong record id."}');
                    return;
                }

                $query = 'DELETE FROM ' . $tableName . ' WHERE id = ' . intval($params['recordId']);

                if ($__mysqliHandler->query($query))
                    echo('{"code": "Data was deleted."}');
                else
                    echo('{"code": "Error delete."}');

                break;
            default:
                echo('{"code": "Not selected action"}');

        }
        break;
    default:
        echo('{"code": "Wrong object."}');

}

function cut($mysqliHandler, $str)
{
    return trim(htmlspecialchars(stripslashes(mysqli_real_escape_string($mysqliHandler, $str))));
}