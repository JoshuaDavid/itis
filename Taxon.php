<?php 
class Taxon {
    public $tsn;

    public function __construct($tsn, $withDetail = true) {
        $this->tsn = $tsn;
    }
    // Not idiomatic PHP, but the only way I could find to get lazy evaluation
    // of this stuff.
    public function __get($prop) {
        switch($prop) {
            case "longname": 
                $this->longname = $this->getLongName();
                return $this->longname;
            case "parenttaxon":
                $this->parenttaxon = $this->getParentTaxon();
                return $this->parenttaxon;
            case "childtaxa":
                $this->childtaxa = $this->getChildTaxa();
                return $this->childtaxa;
            case "kingdom_id":
                list($this->kingdom_id, $this->rank_id) = 
                    $this->getKingdomAndRankIds();
                return $this->kingdom_id;
            case "rank_id":
                list($this->kingdom_id, $this->rank_id) = 
                    $this->getKingdomAndRankIds();
                return $this->rank_id;
            case "required_parent_rank":
                return 0;
            case "vernacular":
                $this->vernacular = $this->getVernacular();
                return $this->vernacular;
            case "kingdom":
                $this->kingdom = $this->getKingdom();
                return $this->kingdom;
            case "rank":
                $this->rank = $this->getRank();
                return $this->rank;
            case "images":
                $this->images = $this->getImages();
                return $this->images;
            case "comments":
                $this->comments = $this->getComments();
                return $this->comments;
        }
    }
    private function getLongName() {
        global $db;
        $query = "select completename from longnames where tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result)) {
            return $result[0]["completename"];
        } else {
            die("No taxon found with tsn=$this->tsn");
        }
    }
    private function getParentTaxon() {
        global $db;
        $query = "select parent_tsn from taxonomic_units where tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result)) {
            $parent_tsn = $result[0]["parent_tsn"];
            if($parent_tsn == 0 or $parent_tsn == $this->tsn) {
                return NULL;
            } else {
                return new Taxon($parent_tsn, false);
            }
        } else {
            return NULL;
        }
    }
    private function getChildTaxa() {
        global $db;
        $query = "select tsn from taxonomic_units where parent_tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result)) {
            $child_taxa = array();
            foreach($result as $row) {
                if($row['tsn'] != $this->tsn) {
                    $child_taxon = $row['tsn'];
                    $child_taxa[] = new Taxon($child_taxon, false);
                }
            }
            return $child_taxa;

        } else {
            return NULL;
        }
    }
    public function getTaxonomicParents() {
        $t = $this;
        $ts = array();
        while($t->parenttaxon != NULL) {
            $ts[] = $t;
            $t = $t->parenttaxon;
        }
        $ts = array_reverse($ts);
        array_pop($ts);
        return $ts;
    }
    private function getKingdomAndRankIds() {
        global $db;
        $query = "select kingdom_id, rank_id from taxonomic_units
            where tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $results = $stmt->fetch();
        $kingdom_id = $results['kingdom_id'];
        $rank_id = $results['rank_id'];
        return array($kingdom_id, $rank_id);
    }
    private function getKingdom() {
        global $db;
        $query = "select kingdom_name from kingdoms
            where kingdom_id=:kingdom_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':kingdom_id', $this->kingdom_id);
        $stmt->execute();
        $results = $stmt->fetch();
        $kingdom = $results['kingdom_name'];
        return $kingdom;
    }
    private function getRank() { 
        global $db;
        $query = "select rank_name from taxon_unit_types
            where rank_id=:rank_id and kingdom_id=:kingdom_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':rank_id', $this->rank_id);
        $stmt->bindParam(':kingdom_id', $this->kingdom_id);
        $stmt->execute();
        $results = $stmt->fetch();
        $rank = $results['rank_name'];
        return $rank;
    }
    public function getVernacular() {
        global $db;
        $query = "select vernacular_name from vernaculars
            where tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $results = $stmt->fetch();
        $rank = $results['vernacular_name'];
        return $rank;
    }
    public function getComments() {
        global $db;
        // Azathoth was here. But the query works.
        $query = "select comments.comment_detail 
            from comments inner join tu_comments_links 
                on comments.comment_id = tu_comments_links.comment_id 
            where tu_comments_links.tsn=:tsn;";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $comments = array();
        foreach($results as $row) {
            $comments[] = $row['comment_detail'];
        }
        return $comments;
    }
    public function getImages() {
        global $db;
        $query = "select tsn, alt, location from images
            where tsn=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $images = $stmt->fetchAll();
        return $images;
    }
    public function getLink() {
        return "$this->rank: <a href='species?tsn=$this->tsn'>$this->longname</a>";
    }
}
