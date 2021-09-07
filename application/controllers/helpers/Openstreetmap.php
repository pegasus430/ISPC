<?php
/**
 * 
 * @author claudiu✍ 
 * Feb 28, 2019
 * 
 * BEWARE ! when you calculate something from pointA -> pointB is NOT the same as pointB -> pointA
 * 
 * BEWARE osrm returns json distance and drivetime numbers with commma, i've used str_replace for that...
 * TODO change to use NumberFormatter for that
 * 
 * 
 * usage examples from a Zend_Controller_Action :
 * 
 * http://project-osrm.org/docs/v5.5.1/api/#route-service
 * Finds the fastest route between coordinates in the supplied order.
 * 
 * $route = $this->getHelper('Openstreetmap')->route($points);
 * $routeLength = $this->getHelper('Openstreetmap')->routeLength([... $pointN]);
 * $routeLength = $this->getHelper('Openstreetmap')->routeLength($pointA, $pointB);
 * $routeDistance = $this->getHelper('Openstreetmap')->routeDistance([... $pointN]);
 * $routeDistance = $this->getHelper('Openstreetmap')->routeDistance($pointA, $pointB);
 * $routeDuration = $this->getHelper('Openstreetmap')->routeDuration([... $pointN]);
 * $routeDuration = $this->getHelper('Openstreetmap')->routeDuration($pointA, $pointB);
 * 
 * 
 * 
 * 
 * http://project-osrm.org/docs/v5.5.1/api/#trip-service
 * The trip plugin solves the Traveling Salesman Problem using a greedy heuristic (farthest-insertion algorithm). 
 * The returned path does not have to be the fastest path, as TSP is NP-hard it is only an approximation. 
 * Note that if the input coordinates can not be joined by a single trip (e.g. the coordinates are on several disconnected islands) multiple trips for each connected component are returned.
 *
 * $trip = $this->getHelper('Openstreetmap')->trip($points); 
 * $tripLength = $this->getHelper('Openstreetmap')->tripLength([... $pointN]);
 * $tripLength = $this->getHelper('Openstreetmap')->tripLength($pointA, $pointB);
 * $tripDistance = $this->getHelper('Openstreetmap')->tripDistance([... $pointN]);
 * $tripDistance = $this->getHelper('Openstreetmap')->tripDistance($pointA, $pointB);
 * $tripDuration = $this->getHelper('Openstreetmap')->tripDuration([... $pointN]);
 * $tripDuration = $this->getHelper('Openstreetmap')->tripDuration($pointA, $pointB);
 *
 */
class Application_Controller_Helper_Openstreetmap extends Zend_Controller_Action_Helper_Abstract
{
    
    
    /**
     * 
     * @var array from bootstrap options
     */
    private $__config = [];
    
    
    /**
     * 
     * @var Zend_Http_Client
     */
    private $__httpService = NULL;
    
    
    /**
     * 
     * @var Zend_Uri_Http|string
     */
    private $__httpServiceUrl = NULL;
    
    
    public function __construct() 
    {
        $this->__config = $this->getFrontController()->getParam('bootstrap')->getOption('openstreetmap');
        
        $this->_httpServiceInit();
        
    }
    
    
    /**
     * Strategy pattern: allow calling helper as broker method
     * Beware, this only return a route from a to b
     * 
     * @param string $pointA
     * @param string $pointB
     * @return multitype|NULL
     */
    public function direct($pointA = '', $pointB = '')
    {
        if ( ! empty($pointA) && ! empty($pointB) && is_string($pointA) && is_string($pointB))
            
            return $this->route([$pointA, $pointB]);
        
        else
             
            return null; 
    }
    
    
    /**
     * 
     * @param array $points (point can be string addres or [lat,long])
     * @param string $getSteps
     * @return multitype
     */
    public function route($points = [], $getSteps = true)
    {
        $route = $this->_osrmService($points, 'route', true);
        
        return $route;
    }
    

    /**
     *
     * @param array $points (point can be string addres or [lat,long])
     * @param string $getSteps
     * @return multitype
     */
    public function trip($points = [], $getSteps = true)
    {
        $trip = $this->_osrmService($points, 'trip', true);
        
        return $trip;
    }
    

    /**
     * 
     * @param array $points (point can be string addres or [lat,long])
     * @return multitype
     */
    public function routeLength($points = [])
    {
        $routeLength = [
            'success'           => false,
            'distance'          => null, 
            'duration'          => null,
            'points'            => null,
            'pointsAsLatLong'   => null
        ];
        
        if (func_num_args() > 1)
            $points = func_get_args($points);
        
        $route = $this->route($points, false);
        
        if ($route['success'] && isset($route['route']['distance'])) {
            $routeLength = [
                'success'   => true, 
                'distance'  => number_format($route['route']['distance'], 2, '.', ''),
                'duration'  => number_format($route['route']['duration'], 2, '.', ''),
                'points'    => $route['points'],
                'pointsAsLatLong'    => $route['pointsAsLatLong'],
            ];
        }
        
        return $routeLength;
    }
    
    
    
    /**
     * 
     * @param array $points (point can be string address or [lat,long])
     * @return Ambigous <NULL, number>
     */
    public function routeDistance($points = []) 
    {
        $distance = null;
        
        if (func_num_args() > 1)
            $points = func_get_args($points);
        
        
        $route = $this->route($points, false);
        
        if ($route['success'] && isset($route['route']['distance'])) {
            $distance = number_format($route['route']['distance'], 2, '.', '');
        }
        
        return $distance;
    }
    
    
    /**
     * 
     * @param array $points (point can be string address or [lat,long])
     * @return Ambigous <NULL, number>
     */
    public function routeDuration($points = []) 
    {
        $duration = null;
        
        if (func_num_args()>1) 
            $points = func_get_args($points);
        
        $route = $this->route($points, false);
        
        if ($route['success'] && isset($route['route']['duration'])) {
            $duration = number_format($route['route']['duration'], 2, '.', '');
        }
        
        return $duration;
    }
    
    
    
    
    
    
    
    
    
    /**
     *
     * @param array $points (point can be string addres or [lat,long])
     * @return multitype
     */
    public function tripLength($points = [])
    {
        $routeLength = [
            'success'           => false,
            'distance'          => null,
            'duration'          => null,
            'points'            => null,
            'pointsAsLatLong'   => null
        ];
    
        if (func_num_args() > 1)
            $points = func_get_args($points);
    
        $trip = $this->trip($points, false);
    
        if ($trip['success'] && isset($trip['trip']['distance'])) {
            $routeLength = [
                'success'   => true,
                'distance'  => number_format($trip['trip']['distance'], 2, '.', ''),
                'duration'  => number_format($trip['trip']['duration'], 2, '.', ''),
                'points'    => $trip['points'],
                'pointsAsLatLong'    => $trip['pointsAsLatLong'],
            ];
        }
    
        return $routeLength;
    }
    
    
    
    /**
     * 
     * @param array $points (point can be string address or [lat,long])
     * @return Ambigous <NULL, number>
     */
    public function tripDistance($points = [])
    {
        $distance = null;
    
        if (func_num_args() > 1)
            $points = func_get_args($points);
    
    
        $trip = $this->trip($points, false);
    
        if ($trip['success'] && isset($trip['trip']['distance'])) {
            $distance = number_format($trip['trip']['distance'], 2, '.', '');
        }
    
        return $distance;
    }
    
    
    
    /**
     * 
     * @param array $points (point can be string address or [lat,long])
     * @return Ambigous <NULL, number>
     */
    public function tripDuration($points = [])
    {
        $duration = null;
    
        if (func_num_args()>1)
            $points = func_get_args($points);
    
        $trip = $this->trip($points, false);
    
        if ($trip['success'] && isset($trip['trip']['duration'])) {
            $duration = number_format($trip['trip']['duration'], 2, '.', '');
        }
    
        return $duration;
    }
    
    
    
    
    
    
    
    
    
    
    
    

    
    /**
     * 
     * ! ATTENTION multiple return points
     * 
     * ex : ->route([ 'address1' , 'address2' , [address3Lat, address3Long] , address4 , [addr5Lat, addr5Long ] , .. ])
     *
     * 
     * @param array $points (point can be string address or [lat,long])
     * @param boolean $getSteps
     * @return multitype
     */
    public function _osrmService($points = [], $service = 'route', $getSteps = false) {
        
        $osrmService = [
            'success'           => false,
            'points'            => $points,
            'errors'            => null,
            'message'           => null,
            "{$service}"        => null,
            'pointsAsLatLong'   => null
        ];
        
        
        if (count($points) < 2) {
            $osrmService['message'] = 'you need at least to go from pointA to pointB';
            return $osrmService; //failsafe, you need at least to go from pointA to pointB
        }
        

        $pointsAsLatLong = $this->_parsePointsAsLatLong($points);
        
        
        if ( ! $pointsAsLatLong) {
            
            $osrmService['message'] = 'geocode failed';
            $osrmService['errors'] = $this->__pointsAsLatLongERROR;
            
            return $osrmService;
        }
        
        /*
         * BEWARE
         * ORSM documentation states the order is lat,long; .... but for me it only worked the other way around.. long,lat;
         */
        $pointsAsString = implode(";", array_map(function($p){ 
            $p['lat'] = isset($p['lat']) ? $p['lat'] : reset($p); 
            $p['lon'] = isset($p['lon']) ? $p['lon'] : end($p); 
            return( "{$p['lon']},{$p['lat']}");
        }, $pointsAsLatLong));
        
        
        /*
         * v1 = version
         * car = profile
         */
        $this->__httpServiceUrl = "{$this->__config['router']['serviceUrl']}{$service}/v1/car/";
        
        $this->__httpServiceUrl .= $pointsAsString;
        
        //do the curl fetch
        $result = $this->_httpServiceRequest([
            'overview'      => 'false',
            'steps'         => $getSteps ? 'true' : 'false',
        ]);
        
        $resultArr = null;
        
        //parse curl result
        try {
            Zend_Json::$useBuiltinEncoderDecoder = true;
            $resultArr = Zend_Json::decode($result);
        } catch (Exception $e) {
            $osrmService['message'] = 'json decode failed';
            return $osrmService;
        }
        if ($result && $resultArr && $resultArr['code'] == 'Ok' && isset($resultArr["{$service}s"][0])) {
            $osrmService['success']   = true;
            $osrmService['message']   = 'Ok';
            $osrmService[$service]     = $resultArr["{$service}s"][0];
            $osrmService['pointsAsLatLong'] = $pointsAsLatLong;
        } else {
            $osrmService['message'] = 'route not found';
        }
        
        return $osrmService;
    }
    
    
    
    
    
    private function _parsePointsAsLatLong($points = [])
    {
        $pointsAsLatLong = [];
    
        if ( ! is_array($points))
            return null; //failsafe
    
        $parsePoints = true;
    
        foreach ($points as $i => $point)
        {
            if (is_string($point) && ! empty($point)) { //you provided a string address.. try to geocode this
                $point = $this->geocode($point);
            }
    
            if (is_array($point))
            {
                $point = array_filter($point, 'is_numeric');
    
                if (count($point) != 2) {
                    $pointsAsLatLong[$i] = "this is not a correct latLong";
                    $parsePoints = false;
                    break;
                } else {
                    $pointsAsLatLong[$i] = $point;
                }
            } else {
                $pointsAsLatLong[$i] = "cannot identify address";
                $parsePoints = false;
                break;
            }
        }
         
        if ( ! $parsePoints) {
            $this->__pointsAsLatLongERROR = $pointsAsLatLong;
            return null;
        }
    
        $this->__pointsAsLatLongERROR = NULL;
    
        return $pointsAsLatLong;
    }
    

    /**
     * get latitude longitude from a address string
     */
    public function geocode($address = '')
    {
        $latLong = ['lat' => null, 'lon' => null];
    
        if ( empty($address) 
            || ! is_string($address) 
            || ! isset($this->__config['geocoder']) || empty($this->__config['geocoder']['serviceUrl'])) 
        {
            return $latLong; //fail-safe
        }
        
        $this->__httpServiceUrl = $this->__config['geocoder']['serviceUrl'] . "search/";
        
        
        $result = $this->_httpServiceRequest([
            'q'         => $address,
            'limit'     => 1,
            'format'    => 'json',
            'addressdetails'    =>1,
            
        ]);
        
        $resultArr = null;
        
        try {
            
            Zend_Json::$useBuiltinEncoderDecoder = true;
            $resultArr = Zend_Json::decode($result);
            
        } catch (Exception $e) {
            
            return $latLong;//fail-safe
        }

        if ($result && $resultArr && isset($resultArr[0]) && isset($resultArr[0]['lat'])) {
            
            $latLong = ['lat' => $resultArr[0]['lat'], 'lon' => $resultArr[0]['lon']];
        }
        
        return  $latLong;
    }
    
    
    /**
     * TODO
     * get address from latitude longitude
     */
    public function reversegeocode($lat, $long)
    {
        $address = null;
        
        $this->__httpServiceUrl = $this->__config['geocoder']['serviceUrl'] . "reverse/";
        
        return $address;
    }
    
    
    
    private function _httpServiceInit() 
    {
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_MAXREDIRS      => 0,
                CURLOPT_RETURNTRANSFER  => true,
                 
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                 
                CURLOPT_TIMEOUT => 15,
                CURLINFO_CONNECT_TIME => 16,
                CURLOPT_CONNECTTIMEOUT => 17,
                // 	            CURLOPT_COOKIE => $_req_cookie,
            )
        ));
     
        $httpConfig = array(
            'timeout'      => 20,// Default = 10
            'useragent'    => 'Zend_Http_Client-ISPC-FETCH-ROUTE',// Default = Zend_Http_Client
            'keepalive'    => true,
        );
        
        $this->__httpService =  new Zend_Http_Client(null, $httpConfig);
        $this->__httpService->setAdapter($adapter);
        $this->__httpService->setCookieJar(true);
        $this->__httpService->setMethod('GET');
    }
    
    
    private function _httpServiceRequest($params = [])
    {
        try {
            $this->__httpService->setUri($this->__httpServiceUrl);
            
            $this->__httpService->setParameterGet($params);
            
            $response = $this->__httpService->request();
            
            $this->__httpService->resetParameters();
            
            if ($response->isError()) {
                
                return null;
                
            } else {
                
                return $response->getBody();
            }
            
        }catch (Exception $e) {
            return null;
        }
    }
    
    
    
    
    
    /**
     * $points = [
            'Karl Meyer Rohstoffverwertung, Weidenweg, Fhain, Friedrichshain-Kreuzberg, Berlin, 10249, Germany',
            '42, Kulmseestraße, Biesdorf Nord, Biesdorf, Marzahn-Hellersdorf, Berlin, 12683, Germany',
            '26, Schönhauser Allee, Kollwitzkiez, Prenzlauer Berg, Pankow, Berlin, 10435, Germany',
            'Lützowplatz 17, 10785 Berlin, Germany',
            'Karl-Liebknecht-Str. 32a Berlin, Germany',
            'Bernhard-Weiss-Str. 5 Berlin, Germany',
            'Theanolte-Bähnisch-Straße 2 Berlin, Germany',
        ];
        
        $routeDistance = $this->getHelper('Openstreetmap')->routeDistance($points[0], $points[1], $points[3]);
        $routeDuration = $this->getHelper('Openstreetmap')->routeDuration([$points[0], $points[1], $points[3]]);
//         $routeLength = $this->getHelper('Openstreetmap')->routeLength($points);
//         $route = $this->getHelper('Openstreetmap')->route($points);
        
        $tripDistance = $this->getHelper('Openstreetmap')->tripDistance($points[0], $points[1], $points[3]);
        $tripDuration = $this->getHelper('Openstreetmap')->tripDuration([$points[0], $points[1], $points[3]]);
//         $tripLength = $this->getHelper('Openstreetmap')->tripLength($points);
//         $trip = $this->getHelper('Openstreetmap')->trip($points);
        
        
        dd( $routeDistance , $tripDistance, $routeDuration ,$tripDuration);
        dd( $route, $trip);
        dd($this->_helper->openstreetmap($points[0], $points[1]));
     */
    
}