<?php

class RedoGetLatLngFromGoogleUsingAddressTask extends BuildTask
{
    protected $title = "Re-attempt to convert address into google map co-ordinates";

    protected $description = "Runs through all the failed GetLatLngFromGoogleUsingAddress";

    /**
     * @var boolean
     */
    protected $verbose = true;

    /**
     * @param boolean
     */
    public function setVerbose($b)
    {
        $this->verbose = $b;
    }

    private $step = 100;

    public function run($request)
    {
        if ($this->verbose) {
            set_time_limit(3600);
            increase_time_limit_to(3600);
            increase_memory_limit_to('512M');
            Config::inst()->update("GetLatLngFromGoogleUsingAddress", "debug", true);
        }
        if ($this->verbose) {
            echo "<h1>============= Start =============</h1>";
            flush();
            ob_flush();
        }
        if ($this->verbose) {
            $objects = GoogleMapLocationsObject::get()->where("(Longitude IS NULL OR Latitude IS NULL) AND (Address IS NOT NULL AND Address <> '')")->count();
            echo "<h3>============= Redoing $count Objects =============</h3>";
            flush();
            ob_flush();
        }
        for ($i = 0; $i < 1000000; $i = $i + $this->step) {
            $objects = GoogleMapLocationsObject::get()->where("(Longitude IS NULL OR Latitude IS NULL) AND (Address IS NOT NULL AND Address <> '')")->limit($this->step, $i);
            if ($objects = GoogleMapLocationsObject::get()->where("(Longitude IS NULL OR Latitude IS NULL) AND (Address IS NOT NULL AND Address <> '')")->limit($this->step, $i)->count() == 0) {
                $i = 99999999999;
            }
            foreach ($objects as $object) {
                DB::alteration_message("<hr /><hr /><hr /><h1>Looking up ".$object->Address."</h1>");
                $object->FullAddress = '';
                $object->write();
                if ($object->Longitude) {
                    if ($this->verbose) {
                        DB::alteration_message(" --- found ".$object->FullAddress, "created");
                        ;
                        flush();
                        ob_flush();
                    }
                } else {
                    if ($this->verbose) {
                        DB::alteration_message(" --- not found ".$object->Address, "deleted");
                        ;
                        flush();
                        ob_flush();
                    }
                }
            }
            if ($this->verbose) {
                echo "<h1>============= END =============</h1>";
                flush();
                ob_flush();
            }
        }
    }
}
