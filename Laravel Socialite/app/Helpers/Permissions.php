<?php
namespace App\Helpers;

use App\Http\Middleware\IvaoDivisionMiddleware;
use App\Http\Middleware\IvaoRoleMiddleware;

class Permissions
{
    // Add cache layer to avoid too many loops
    private static $is_hqatc = null;
    private static $is_atc = null;
    private static $is_hqfo = null;
    private static $is_fo = null;
    private static $is_so = null;
    private static $is_hqso = null;
    private static $is_training = null;
    private static $is_admin = null;
    private static $is_sector = null;
    private static $is_divisionalOnly = null;
    private static $is_allowAirportsActions = null;
    private static $is_allowAtcPositionsActions = null;
    private static $is_allowCentersActions = null;
    private static $is_allowSubCentersActions = null;
    private static $is_allowNotamsActions = null;
    private static $is_allowAirlinesActions = null;
    private static $is_allowVirtualAirlinesActions = null;
    private static $is_allowSectors = null;
    private static $is_allowRoutes = null;
    private static $is_allowAntennas = null;
    private static $is_softwaredeveloper = null;
    private static $is_mtldeveloper = null;

    static function mtlDeveloper()
    {
        if (Permissions::$is_mtldeveloper != null) {
            return Permissions::$is_mtldeveloper;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'mtl_developer'];
        Permissions::$is_mtldeveloper = Permissions::check($perms, $required);
        return Permissions::$is_mtldeveloper;
    }

    static function softwareDeveloper()
    {
        if (Permissions::$is_softwaredeveloper != null) {
            return Permissions::$is_softwaredeveloper;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'software_developer'];
        Permissions::$is_softwaredeveloper = Permissions::check($perms, $required);
        return Permissions::$is_softwaredeveloper;
    }

    static function hqatc()
    {
        if (Permissions::$is_hqatc != null) {
            return Permissions::$is_hqatc;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-ATC'];
        Permissions::$is_hqatc = Permissions::check($perms, $required);
        return Permissions::$is_hqatc;
    }

    static function atc()
    {
        if (Permissions::$is_atc != null) {
            return Permissions::$is_atc;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-ATC', 'DIV-HQ', 'DIV-ATC'];
        Permissions::$is_atc = Permissions::check($perms, $required);
        return Permissions::$is_atc;
    }

    static function hqfo()
    {
        if (Permissions::$is_hqfo != null) {
            return Permissions::$is_hqfo;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-FO'];
        Permissions::$is_hqfo = Permissions::check($perms, $required);
        return Permissions::$is_hqfo;
    }

    static function fo()
    {
        if (Permissions::$is_fo != null) {
            return Permissions::$is_fo;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-FO', 'DIV-HQ', 'DIV-FO'];
        Permissions::$is_fo = Permissions::check($perms, $required);
        return Permissions::$is_fo;
    }

    static function hqso()
    {
        if (Permissions::$is_hqso != null) {
            return Permissions::$is_hqso;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-SO'];
        Permissions::$is_hqso = Permissions::check($perms, $required);
        return Permissions::$is_hqso;
    }

    static function so()
    {
        if (Permissions::$is_so != null) {
            return Permissions::$is_so;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'HQ-SO', 'DIV-HQ', 'DIV-SO'];
        Permissions::$is_so = Permissions::check($perms, $required);
        return Permissions::$is_so;
    }

    static function training()
    {
        if (Permissions::$is_training != null) {
            return Permissions::$is_training;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'DIV-TD'];
        Permissions::$is_training = Permissions::check($perms, $required);
        return Permissions::$is_training;
    }

    static function admin()
    {
        if (Permissions::$is_admin != null) {
            return Permissions::$is_admin;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator'];
        Permissions::$is_admin = Permissions::check($perms, $required);
        return Permissions::$is_admin;
    }

    static function sector()
    {
        if (Permissions::$is_sector != null) {
            return Permissions::$is_sector;
        }

        $perms = request()->session()->get('ivaoPerms');
        $required = ['administrator', 'web_developer', 'sector_developer', 'HQ-ATC', 'DIV-HQ', 'DIV-ATC', 'DIV-FIR'];
        Permissions::$is_sector = Permissions::check($perms, $required);
        return Permissions::$is_sector;
    }

    static function divisionalOnly()
    {
        if (Permissions::$is_divisionalOnly != null) {
            return Permissions::$is_divisionalOnly;
        }

        $perms = request()->session()->get('ivaoPerms');
        $perms_list = ['administrator', 'web_developer', 'sector_developer', 'HQ-ATC', 'HQ-FO', 'HQ-SO'];

        if (Permissions::check($perms, $perms_list)) {
            Permissions::$is_divisionalOnly = false;
            return Permissions::$is_divisionalOnly;
        }

        foreach ($perms as $key => $value) {
            if (strpos($key, 'DIV') !== false && $value) {
                Permissions::$is_divisionalOnly = true;
                return Permissions::$is_divisionalOnly;
            }
        }

        Permissions::$is_divisionalOnly = false;
        return Permissions::$is_divisionalOnly;
    }

    static function allowAirportsActions(){
        if (Permissions::$is_allowAirportsActions != null) {
            return Permissions::$is_allowAirportsActions;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowAirportsActions = true;
            return true;
        }

        $allow_airport_So = IvaoRoleMiddleware::allow_airport_so(request(), request()->session()->get('ivaoData'));
        if (Permissions::so() && !Permissions::divisionalOnly() && $allow_airport_So){
            Permissions::$is_allowAirportsActions = true;
            return true;
        }

        $perms = request()->session()->get('ivaoPerms');
        $allow_airport = IvaoDivisionMiddleware::allow_airport(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-ATC'] || $perms['DIV-HQ']) && $allow_airport){
            Permissions::$is_allowAirportsActions = true;
            return true;
        }

        Permissions::$is_allowAirportsActions = $perms['DIV-SO'] && $allow_airport && $allow_airport_So;
        return Permissions::$is_allowAirportsActions;
    }

    static function allowAtcPositionsActions(){
        if (Permissions::$is_allowAtcPositionsActions != null) {
            return Permissions::$is_allowAtcPositionsActions;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowAtcPositionsActions = true;
            return true;
        }

        $allow_atcposition_So = IvaoRoleMiddleware::allow_atcposition_so(request(), request()->session()->get('ivaoData'));
        if (Permissions::so() && !Permissions::divisionalOnly() && $allow_atcposition_So){
            Permissions::$is_allowAtcPositionsActions = true;
            return true;
        }

        $perms = request()->session()->get('ivaoPerms');
        $allow_atc_position = IvaoDivisionMiddleware::allow_atcposition(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-ATC'] || $perms['DIV-HQ']) && $allow_atc_position){
            Permissions::$is_allowAtcPositionsActions = true;
            return true;
        }

        Permissions::$is_allowAtcPositionsActions = $perms['DIV-SO'] && $allow_atc_position && $allow_atcposition_So;
        return Permissions::$is_allowAtcPositionsActions;
    }

    static function allowCentersActions(){
        if (Permissions::$is_allowCentersActions != null) {
            return Permissions::$is_allowCentersActions;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowCentersActions = true;
            return true;
        }

        $allow_center_So = IvaoRoleMiddleware::allow_centers_so(request(), request()->session()->get('ivaoData'));
        if (Permissions::so() && !Permissions::divisionalOnly() && $allow_center_So){
            Permissions::$is_allowCentersActions = true;
            return true;
        }

        $perms = request()->session()->get('ivaoPerms');
        $allow_center = IvaoDivisionMiddleware::allow_centers(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-ATC'] || $perms['DIV-HQ']) && $allow_center){
            Permissions::$is_allowCentersActions = true;
            return true;
        }

        Permissions::$is_allowCentersActions = $perms['DIV-SO'] && $allow_center && $allow_center_So;
        return Permissions::$is_allowCentersActions;
    }

    static function allowSubCentersActions(){
        if (Permissions::$is_allowSubCentersActions != null) {
            return Permissions::$is_allowSubCentersActions;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowSubCentersActions = true;
            return true;
        }

        $allow_subcenter_so = IvaoRoleMiddleware::allow_subcenter_so(request(), request()->session()->get('ivaoData'));
        if (Permissions::so() && !Permissions::divisionalOnly() && $allow_subcenter_so){
            Permissions::$is_allowSubCentersActions = true;
            return true;
        }

        $perms = request()->session()->get('ivaoPerms');
        $allow_subcenter = IvaoDivisionMiddleware::allow_subcenters(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-ATC'] || $perms['DIV-HQ']) && $allow_subcenter){
            Permissions::$is_allowSubCentersActions = true;
            return true;
        }

        Permissions::$is_allowSubCentersActions = $perms['DIV-SO'] && $allow_subcenter && $allow_subcenter_so;
        return Permissions::$is_allowSubCentersActions;

    }

    static function allowNotamsActions(){
        if (Permissions::$is_allowNotamsActions != null) {
            return Permissions::$is_allowNotamsActions;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowNotamsActions = true;
            return true;
        }

        $allow_notam_So = IvaoRoleMiddleware::allow_notams_so(request(), request()->session()->get('ivaoData'));
        if (Permissions::so() && !Permissions::divisionalOnly() && $allow_notam_So){
            Permissions::$is_allowNotamsActions = true;
            return true;
        }

        $perms = request()->session()->get('ivaoPerms');
        $allow_notam = IvaoDivisionMiddleware::allow_notams(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-ATC'] || $perms['DIV-HQ']) && $allow_notam){
            Permissions::$is_allowNotamsActions = true;
            return true;
        }

        Permissions::$is_allowNotamsActions = $perms['DIV-SO'] && $allow_notam && $allow_notam_So;
        return Permissions::$is_allowNotamsActions;
    }

    static function allowAirlinesActions(){
        if (Permissions::$is_allowAirlinesActions != null) {
            return Permissions::$is_allowAirlinesActions;
        }

        if (Permissions::fo() && !Permissions::divisionalOnly()){
            Permissions::$is_allowAirlinesActions = true;
            return true;
        }


        $perms = request()->session()->get('ivaoPerms');
        $allow_airline = IvaoDivisionMiddleware::allow_airline(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-FO'] || $perms['DIV-HQ']) && $allow_airline){
            Permissions::$is_allowAirlinesActions = true;
            return true;
        }

        Permissions::$is_allowAirlinesActions = $allow_airline;
        return Permissions::$is_allowAirlinesActions;
    }

    static function allowVirtualAirlinesActions(){
        if (Permissions::$is_allowVirtualAirlinesActions != null) {
            return Permissions::$is_allowVirtualAirlinesActions;
        }

        if (Permissions::fo() && !Permissions::divisionalOnly()){
            Permissions::$is_allowVirtualAirlinesActions = true;
            return true;
        }


        $perms = request()->session()->get('ivaoPerms');
        $allow_virtualAirline = IvaoDivisionMiddleware::allow_virtualAirline(request(), request()->session()->get('ivaoData'));
        if ( ($perms['DIV-FO'] || $perms['DIV-HQ']) && $allow_virtualAirline){
            Permissions::$is_allowVirtualAirlinesActions = true;
            return true;
        }

        Permissions::$is_allowVirtualAirlinesActions = $allow_virtualAirline;
        return Permissions::$is_allowVirtualAirlinesActions;
    }

    static function allowSectors(){
        if (Permissions::$is_allowSectors != null) {
            return Permissions::$is_allowSectors;
        }

        if (Permissions::atc() && !Permissions::divisionalOnly()){
            Permissions::$is_allowSectors = true;
            return true;
        }

        Permissions::$is_allowSectors = IvaoDivisionMiddleware::allow_sectors(request(), request()->session()->get('ivaoData'));
        return Permissions::$is_allowSectors;
    }

    static function allowRoutes(){
        if (Permissions::$is_allowRoutes != null) {
            return Permissions::$is_allowRoutes;
        }

        if (Permissions::fo() && !Permissions::divisionalOnly()){
            Permissions::$is_allowRoutes = true;
            return true;
        }

        Permissions::$is_allowRoutes = IvaoDivisionMiddleware::allow_route(request(), request()->session()->get('ivaoData'));
        return Permissions::$is_allowRoutes;
    }

    static function allowAntennas($antennaCountry = null)
    {
        if(Permissions::$is_allowAntennas != null)
        {
            return Permissions::$is_allowAntennas;
        }

        if(Permissions::hqatc() || Permissions::atc())
        {
            Permissions::$is_allowAntennas = true;
            return true;
        }

        Permissions::$is_allowAntennas = IvaoDivisionMiddleware::allow_antenna(request(), request()->session()->get('ivaoData'));
        return Permissions::$is_allowAntennas;
    }

    public static function check($perms, $ids)
    {
        for ($i = 0; $i < count($ids); $i++)
        {
            $perm = $ids[$i];
            if (isset($perms[$perm]) && $perms[$perm])
            {
                return true;
            }
        }

        return false;
    }

}
