<?php
namespace Tsp\Irangardi;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Method;
use Poirot\Connection\Interfaces\iConnection;
use Poirot\Std\Interfaces\Struct\iDataStruct;
use Poirot\Std\Interfaces\ipOptionsProvider;
use Tsp\Irangardi\HotelService\HotelServiceOpts;
use Tsp\Irangardi\HotelService\SoapPlatform;
use Tsp\Irangardi\Interfaces\iIRHotel;
use Tsp\SoapTransporter;

class HotelService extends AbstractClient
    implements ipOptionsProvider
    , iIRHotel
{
    /** @var HotelServiceOpts */
    protected $options;

    /**
     * HotelService constructor.
     * @param iDataStruct|array $options
     */
    function __construct($options = null)
    {
        if ($options != null)
            $this->optsData()->from($options);
    }

    // Client API:

    /**
     * To receive the list of cities/code that exist in our system.
     * @return iResponse
     */
    function getCityList()
    {
        $method = $this->newMethod(__FUNCTION__);
        return $this->call($method);
    }

    /**
     * To Get Hotels List In A City
     * @param string $CityCod
     * @return iResponse
     */
    function getHotelListCity($CityCod)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'CityCod' => $CityCod,
        ]);
        return $this->call($method);
    }

    /**
     * Get All Available Hotels
     * @return iResponse
     */
    function getAllHotelList()
    {
        $method = $this->newMethod(__FUNCTION__);
        return $this->call($method);
    }

    /**
     * Get Detail Properties Of Hotel By HotelCode
     * @param string $HotelCod
     * @return iResponse
     */
    function getHotelProperties($HotelCod)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'HotelCod' => $HotelCod,
        ]);
        return $this->call($method);
    }

    /**
     * Retrieve Available Room Types Of Hotel
     * @param $HotelCod
     * @return iResponse
     */
    function getRoomList($HotelCod)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'HotelCod' => $HotelCod,
        ]);
        return $this->call($method);
    }

    /**
     * Get Room Price For A Given Date
     *
     * @param string $HotelCod Hotel Code
     * @param string $Dat     Jalali Date in "yyyymmdd" format
     * @param int $RoomTypeCod The Room Code From getRoomList Method
     *                         if not passed it will return all available
     *                         rooms price
     * @return iResponse
     */
    function getRoomPriceListOneDate($HotelCod, $Dat, $RoomTypeCod = 0)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'HotelCod'    => $HotelCod,
            'Dat'         => $Dat,
            'RoomTypeCod' => $RoomTypeCod,
        ]);
        return $this->call($method);
    }

    /**
     * Retrieve The Price For A Room On A Specific Date
     * @param string $HotelCod    Hotel Code
     * @param string $Dat         Jalali Date in "yyyymmdd" format
     * @param int    $RoomTypeCod The Room Code From getRoomList Method
     * @param int    $ResLong     Stay Duration, exp 3 mean three night long
     * @return iResponse
     */
    function getRoomPriceListDateResLong($HotelCod, $Dat, $RoomTypeCod, $ResLong = 1)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'HotelCod'    => $HotelCod,
            'Dat'         => $Dat,
            'RoomTypeCod' => $RoomTypeCod,
            'ResLong'     => $ResLong,
        ]);
        return $this->call($method);
    }

    /**
     * Reserve Room As Temporary For 15min
     *
     * ! it must approved with another related method calls
     *
     * @param string $HotelCod    Hotel Code
     * @param string $StDat       Jalali Date in "yyyymmdd" format
     * @param int    $RoomTypeCod The Room Code From getRoomList Method
     * @param int    $ResLong     Stay Duration, exp 3 mean three night long. (1~10)
     * @param int    $NumRoom     Number Of Rooms To Reserve (1~10)
     *
     * @return iResponse Exception or Reserve Number
     */
    function reserveRoom($HotelCod, $StDat, $RoomTypeCod, $ResLong = 1, $NumRoom = 1)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'HotelCod'    => $HotelCod,
            'StDat'       => $StDat,
            'RoomTypeCod' => $RoomTypeCod,
            'ResLong'     => $ResLong,
            'NumRoom'     => $NumRoom,
        ]);
        return $this->call($method);
    }

    /**
     * Insert Data Form Reserved Temporary Room
     *
     * !! it must be approved by calling another related method
     *    to finalize
     *
     * @param int              $ReserveNo Reserve Number after ReserveRoom Method Call
     * @param array            $Rooms     Room Type Codes [1, 2, 3]
     * @param array            $CustData  Customer Information [..
     *                              ['name'=>.., 'family'=>.., 'melli_code'=>.., 'birth_date'=>yyymmdd]
     *                         ..]
     * @param string           $MobNo     Mobile Number For Emergency Calls
     * @param string|array     $PhoneNo   Phone Number or [$phone1, $phone2]
     * @param $Address
     * @param string           $Email
     * @param string           $ZipCode
     * @param string           $Dsc
     * @param array            $FlightInfo ['??????RaftAirLine'=>..,'FlightNoRaft'=>..,'TimeRaft'=>..,'MasirRaft'=>..
     *                                      'FlightNoRaft'=>.., 'TimeRaft'=>.., 'MasirRaft'=>.., 'BargashtAirLine'=>..,
     *                                      'FlightNoBargasht'=>.., 'TimeBargasht'=>.., 'MasirBargasht'=>..
     *
     * @return iResponse
     */
    function insertNamReserveRoomTemporary(
        $ReserveNo
        , array $Rooms
        , array $CustData
        , $MobNo
        , $PhoneNo
        , $Address
        , $Email = null
        , $ZipCode = null
        , $Dsc = null
        , $FlightInfo = [

        ]
    ) {
        // We have to send all arguments, event Optionals to SoapServer.
        // elsewhere we got -100 error
        $args = [
                // strict with method call
                // "ReserveNo"   => null,
                // "TypeRoomAll" => null,
                // "Mobile"      => null,
                // "Address"     => null,
                // "TelNo1"      => null,

                // $extraArgs Merge

                // must be sent with empty default value
                "TelNo2"  => '',
                "Email"   => '',
                "ZipCode" => '',
                "Dsc"     => '',

                "RaftAirLine"      => '',
                "FlightNoRaft"     => '',
                "TimeRaft"         => '',
                "MasirRaft"        => '',
                "BargashtAirLine"  => '',
                "FlightNoBargasht" => '',
                "TimeBargasht"     => '',
                "MasirBargasht"    => '',
        ];

        ## customer data arguments
        $custArr   = ['name'=>null, 'family'=>null, 'melli_code'=>null, 'birth_date'=>null];
        $extraArgs = [
            'NamAll'      => [], // coma separated of customer(s) name
            'FamilyAll'   => [], // coma separated of customer(s) family
            'MeliCodAll'  => [], // coma separated of customer(s) Melli Code
            'BirthDatAll' => [], // coma separated of customer(s) Birth Day
        ];
        foreach($CustData as $cus) {
            ### validate data
            if (count(array_intersect_key($cus, $custArr)) != count($custArr))
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Customer Data. given: (%s).'
                    , \Poirot\Std\flatten($cus)
                ));

            $extraArgs['NamAll'][]      = $cus['name'];
            $extraArgs['FamilyAll'][]   = $cus['family'];
            $extraArgs['MeliCodAll'][]  = $cus['melli_code'];
            $extraArgs['BirthDatAll'][] = $cus['birth_date'];
        }
        foreach($extraArgs as &$v)
            ### change data to comma separated form
            $v = implode(',', $v);

        // ...

        $args['ReserveNo']   = $ReserveNo;
        $args['TypeRoomAll'] = implode(', ', $Rooms);
        $args['Mobile']      = $MobNo;
        $args['Address']     = $Address;

        if (is_string($PhoneNo)) $PhoneNo = [(string)$PhoneNo];
        if (isset($PhoneNo[0]))
            $args['TelNo1'] = $PhoneNo[0];
        if (isset($PhoneNo[1]))
            $args['??????TelNo2??????'] = $PhoneNo[1];

        ($Email   === null) ?: $args['Email']   = $Email;
        ($ZipCode === null) ?: $args['ZipCode'] = $ZipCode;
        ($Dsc     === null) ?: $args['Dsc']     = $Dsc;

        ($FlightInfo === null) ?: $args = array_merge(
            $FlightInfo
            , $args
        );

        $args = \Poirot\Std\array_merge($args, $extraArgs);

        $method = $this->newMethod(__FUNCTION__, $args);
        return $this->call($method);
    }

    /**
     * Confirm Reservation After Insert Customer Data
     *
     * @param int $ReserveNo Reserve number
     *
     * @return iResponse
     */
    function foroshRoomFromTemporary($ReserveNo)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'ReserveNo' => $ReserveNo,
        ]);
        return $this->call($method);
    }

    /**
     * Get Voucher Info For Confirmed Order
     *
     * @param int $ReserveNo Reserve number
     *
     * @return iResponse
     */
    function getRefrenceHotel($ReserveNo)
    {
        $method = $this->newMethod(__FUNCTION__, [
            'ReserveNo' => $ReserveNo,
        ]);
        return $this->call($method);
    }

    // Client Implementation:

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return iPlatform
     */
    function platform()
    {
        if (!$this->platform)
            $this->platform = new SoapPlatform($this);

        return $this->platform;
    }

    /**
     * Get Transporter Adapter
     *
     * @return iConnection
     */
    function transporter()
    {
        if (!$this->transporter)
            // with options build transporter
            $this->transporter = new SoapTransporter(array_merge(
                $this->optsData()->getConnConfig()
                , ['server_url' => $this->optsData()->getServerUrl()]
            ));

        return $this->transporter;
    }


    // ...

    /**
     * @return HotelServiceOpts
     */
    function optsData()
    {
        if (!$this->options)
            $this->options = self::newOptsData();

        return $this->options;
    }

    /**
     * @inheritdoc
     *
     * @param null|mixed $builder Builder Options as Constructor
     *
     * @return HotelServiceOpts
     */
    static function newOptsData($builder = null)
    {
        return new HotelServiceOpts;
    }


    protected function newMethod($methodName, array $args = null)
    {
        $method = new Method;

        $method->setMethod($methodName);

        ## account data options
        ## these arguments is mandatory on each call
        $defAccParams = [
            'OprCod'  => $this->optsData()->getOprCod(),
            'CustCod' => $this->optsData()->getCustCod(),
            'PID'     => $this->optsData()->getPID(),
            'Mojavez' => $this->optsData()->getMojavez(),
        ];

        $args = ($args !== null) ? array_merge($defAccParams, $args) : $defAccParams;
        $method->setArguments($args);

        return $method;
    }
}
