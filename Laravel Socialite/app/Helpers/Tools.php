<?php


namespace App\Helpers;


class Tools
{
    static function replace($data)
    {
        $data = str_replace("(", "_", $data);
        $data = str_replace(")", "_", $data);
        $data = str_replace(" ", "_", $data);
        return $data;
    }

    static function generate_api_url($path, $params = array())
    {
        $params["apiKey"] = env('IVAO_API_TOKEN');
        return env('IVAO_API_URL') . $path . '?' . http_build_query($params);
    }

    public static function getSchema($schema)
    {
        $prefix = env('DB_PREFIX');
        $suffix = env('DB_SUFFIX');

        return ($prefix ? $prefix . "_" : "") . $schema . ($suffix ? "_" . $suffix : "");
    }

    public static function getTable($schema, $table)
    {
        return self::getSchema($schema) . "." . $table;
    }

    public static function getField($schema, $table, $field)
    {
        return self::getTable($schema, $table) . "." . $field;
    }

}
