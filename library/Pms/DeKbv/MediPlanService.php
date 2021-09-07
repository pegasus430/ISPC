<?php

// namespace SmartqStandalone;

// require_once( __DIR__ . DIRECTORY_SEPARATOR . 'TcpdfService.php');

// use SmartqStandalone\TcpdfExtended;


/**
 *
 * Usage example:
 * 
 *    $mediplanSrvc = new \SmartqStandalone\MediPlanService();
 *    // or in Symfony: // $mediplanSrvc = $container->get('smartq.mediplan');
 *    $mediplanSrvc->startNewMediPlan();
 *    // or explicitly choosing the type ... // $mediplanSrvc->startNewMediPlan( 'de_kbv_bmp_2' );
 *    $mediplanSrvc->importDataMatrixXml( $testXml ); // see SmartqBundle/MediPlanType/DeKbvBmp2.php
 *    $filePath = $mediplanSrvc->generatePDF( 'my_pdf_%date%_%time%_%uid%.pdf', [
 *        'path' => 'my/temp/path' ,
 *        'dest' => 'F'
 *    ] );
 *
 *
 *
 * 
 */
class MediPlanService
{

    protected $plan = null;

    protected $tcpdfService = null;

    protected $barcodeService = null;

    /**
     * Initiate a new medication plan instance
     *
     * @param  string $orientation _P_ortrait | _L_andscape | empty string for "choose automatically"
     * @param  array  $options     [description]
     * @return [type]              [description]
     */
    public function startNewMediPlan( $type = 'de_kbv_bmp_2', $version = '', $options = [] ) {

        if ( empty( $this->tcpdfService ) ) {
            require_once( __DIR__ . DIRECTORY_SEPARATOR . 'TcpdfService.php');
            $this->tcpdfService = new TcpdfService(); // SmartqStandalone\TcpdfService
        }

        if ( empty( $this->barcodeService ) ) {
            require_once( __DIR__ . DIRECTORY_SEPARATOR . 'BarcodeService.php');
            $this->barcodeService = new BarcodeService(); // SmartqStandalone\BarcodeService
        }

        if ( ! empty( $this->plan ) ) {
            unset( $this->plan );
        }

        /*
        $uidPool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $rndUid  = '';
        for( $i=0; $i<7 ; $i++ ) {
            $rnd = mt_rand( 0, 61 );
            $rndUid .= $uidPool[$rnd];
        }
        /* */

        // snake to camel / de_kbv_bmp_2 => DeKbvBmp2 ... :
        $typeArr   = explode( '_', $type );
        $typeArr   = array_map( '\\ucfirst', $typeArr );
        $camelType = implode( '', $typeArr );

        require_once( __DIR__ . DIRECTORY_SEPARATOR . 'MediPlanType' . DIRECTORY_SEPARATOR . $camelType . '.php');

        $className = '\\SmartqStandalone\\MediPlanType\\' . $camelType;

        $plan = new $className( array_merge( [
                'tcpdf_service'   => $this->tcpdfService ,
                // 'tcpdf_name'      => $type . '_' . $rndUid ,
                'barcode_service' => $this->barcodeService ,
                'version'         => $version ,
                'generic'         => $this
            ] ,
            $options
        ) );

        $this->plan = $plan;

        return $this;

    }

    
    /**
     * Fetches data for medical products by an array/list of PZNs
     *
     * ########################################################
     * ##  ToDo: this could use a good caching mechanism !!  ##
     * ########################################################
     *
     * ATTENTION: the PZNs are translated into
     * 
     * Result example for $this->getPznData( ['11305464','1566347'] ):
     *   [
     *     'status' => 'ok',
     *     'error' => '',
     *     'products' => [
     *       11305464 => [
     *         'name' => 'FOSTER® NEXThaler® 200 Mikrogramm/6 Mikrogramm pro Dosis Pulver zur Inhalation',
     *         'substances' => [
     *           0 => 'Beclometason dipropionat',
     *           1 => 'Formoterol hemifumarat-1-Wasser'
     *         ],
     *         'concentrations' => [
     *           0 => '200 µg',
     *           1 => '6 µg'
     *         ],
     *         'pzn_list' => [
     *           0 => '11305464',
     *           1 => '11305470'
     *         ]
     *       ],
     *       1566347 => [
     *         'name' => 'KALINOR® 1,56g Kalium/2,5g Citrat Brausetabletten',
     *         'substances' => [
     *           0 => 'Kaliumcitrat-1-Wasser',
     *           1 => 'Kaliumhydrogencarbonat'
     *         ],
     *         'concentrations' => [
     *           0 => '2.17 g',
     *           1 => '2 g'
     *         ],
     *         'pzn_list' => [
     *           0 => '2135106',
     *           1 => '1566347',
     *           2 => '7515598',
     *           3 => '1566353'
     *         ]
     *       ]
     *     ]
     *   ]
     *   
     * 
     * @param  array|string  $pznList  [description]
     * @return [type]        [description]
     */
    public function getPznData( $pznList ) {

        $curl = curl_init();

        $url= "http://dev.smart-q.de:7779/rest/pharmindexv2/getProducts/9F95-6JMS-KAUZ-LFCM/SMARTQ05122014/";

        $params = [];

        if ( is_string( $pznList ) ) {
            $pznList = explode( ',', $pznList );
        }

        foreach ( $pznList as &$str ) {
            $str =  ltrim( rtrim( (string) $str ) , ' 0' );
        }
        unset($str);

        $params['pzn_orlist'] = $pznList;

        $params = urlencode( json_encode($params) );
        $params = str_replace( '+', '%20', $params ); // ... just in case

        $url .= $params;

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $resultJson = curl_exec($curl);
        curl_close($curl);

        if ( empty( $resultJson ) ) {
            $products = [];
            foreach ( $pznList as $pzn ) {
                $products[ $pzn ] = [
                    'name' => '(PZN: ' . $pzn . ' )' ,
                    'substances'     => [] ,
                    'concentrations' => []
                ];
            }
            return [
                'status'   => 'error' ,
                'error'    => 'empty result' ,
                'products' => $products
            ];
        } 
        
        $dataArr = json_decode( $resultJson , true );

        if ( 0 != $dataArr['STATUS']['code'] ) {
            $products = [];
            foreach ( $pznList as $pzn ) {
                $products[ $pzn ] = [
                    'name' => '(PZN: ' . $pzn . ' )' ,
                    'substances'     => [] ,
                    'concentrations' => []
                ];
            }
            return [
                'status'  => 'error' ,
                'error'   => $dataArr['STATUS']['message'] ,
                'products' => $products
            ];
        }

        $products = [];
        $prodLength = count( $dataArr['PRODUCT'] );

        if ( count($pznList) !== $prodLength ) {
            $products = [];
            foreach ( $pznList as $pzn ) {
                $products[ $pzn ] = [
                    'name' => '(PZN: ' . $pzn . ' )' ,
                    'substances'     => [] ,
                    'concentrations' => []
                ];
            }
            return [
                'status'  => 'error' ,
                'error'   => 'One or more committed PZN numbers are invalid.' ,
                'products' => $products
            ];
        }

        $pznListRest = $pznList;

        for ( $p=0; $p<$prodLength; $p++ ) {

            $item = $dataArr['PRODUCT'][$p];

            $prod= [
                'name' => $item['NAME'] ,
                'substances'     => [] ,
                'concentrations' => []
            ];

            $ingList = $item['ITEM_LIST'][0]['COMPOSITIONELEMENTS_LIST'];

            $len = (int) $item['ACTIVESUBSTANCE_COUNT'];
            for( $i=0 ; $i<$len ; $i++ ) {

                $ing = $ingList[$i];

                array_push( $prod['substances'] , $ing['MOLECULENAME'] );

                $unitRaw = $ing['MOLECULEUNITCODE'];
                switch ( $unitRaw ) {
                    case 'MCG': $unit = 'µg'; break;
                    default: $unit = strtolower( $unitRaw );  break;
                }

                if ( empty( $ing['MASSTO'] ) ) {
                    array_push( $prod['concentrations'] , $ing['MASSFROM'].' '.$unit );
                } 
                else {
                    $str = $ing['MASSFROM'] . '-' . $ing['MASSTO'];
                    array_push( $prod['concentrations'] , $str . $unit );
                }
                
            }

            $packagePzns = [];
            foreach ( $item['PACKAGE_LIST'] as $package ) {
                $str = ltrim( rtrim( (string) $package['PZN'] ) , ' 0' );
                array_push( $packagePzns, $str );
            }
            $prod['pzn_list'] = $packagePzns;

            $arr = array_intersect( $packagePzns , $pznListRest );
            if ( count($arr) ) {
                $pzn = current($arr);
                unset( $pznListRest[ array_search( $pzn, $pznListRest ) ]);
            }
            else {
                $pzn = $packagePzns[0];
            }

            $products[ $pzn ] = $prod;

        }

        $out = [
            'status'  => 'ok' ,
            'error'   => '' ,
            'products' => $products
        ];

        return $out;

    }


    /**
     * [__call description]
     *
     * TcpdfService acts as a proxy to the actual current TCPDF-instance in use
     * 
     * @param  [type]  $name       method name
     * @param  [type]  $arguments  array of arguments of the original method call
     * @return [type]              (depends on the original function)
     */
    public function __call ( $name , $arguments ) {
        // startNewMediPlan(
        if ( empty($this->plan) ) {
        	die(__METHOD__ . ' : $this->plan is empty - did you forget to do a $that->startNewMediPlan()?');
            throw new \Exception( __METHOD__ . ' : $this->plan is empty - did you forget to do a $that->startNewMediPlan()?', 1 );
        }
        return call_user_func_array( [ $this->plan , $name ] , $arguments );
    }
    /* */




}
