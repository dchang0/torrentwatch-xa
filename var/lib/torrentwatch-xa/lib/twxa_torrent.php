<?php

/*
 * Not yet in use. Part of OOP rewrite.
 */


/**
 * torrent: represents a single torrent file, which may include many episodes or just one
 *
 * @author dchang
 */
class torrent {

    public $ti;
    public $key;
    public $data;
    private $lowestSeasonNumber;
    private $highestSeasonNumber;
    private $lowestEpisodeNumber;
    private $highestEpisodeNumber;
    
    public function isBatch() {
        // true: batch of episodes, including full seasons
        // false: single episode
    }
    
    public function isFullSeason() {
        // either way, isBatch() must be true
        // true: batch of all episodes in one or more full seasons (no need to consider episode numbers when seeking next episode)
        // false: not a full season, but still more than one episode
    }
    
    public function getLatestSeasonNumberInBatch() {
        
    }
    
    public function getLaatestEpisodeNumberInBatch() {
        
    }
    
}
