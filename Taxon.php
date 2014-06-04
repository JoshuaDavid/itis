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
        $query = "select Parent_TSN from hierarchy where TSN=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result)) {
            $parent_tsn = $result[0]["Parent_TSN"];
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
        $query = "select TSN from hierarchy where Parent_TSN=:tsn";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tsn', $this->tsn);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(count($result)) {
            $child_taxa = array();
            foreach($result as $row) {
                if($row['TSN'] != $this->tsn) {
                    $child_taxon = $row['TSN'];
                    $child_taxa[] = new Taxon($child_taxon, false);
                }
            }
            return $child_taxa;

        } else {
            return NULL;
        }
    }
    private function getTaxonomicParents() {
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
    public function __toString() {
        $s = "";
        $s .= '<table>';
        $s .=   '<tr>';
        $s .=     '<th>Name</th>';
        $s .=     "<td>$this->longname</td>";
        $s .=   '</tr>';
        $s .=   '<tr>';
        $s .=     '<th>Taxonomic Parents</th>';
        $s .=     '<td>';
        foreach($this->getTaxonomicParents() as $parent) {
            $s .= "<div><a href='?tsn=$parent->tsn'>$parent->longname</a></div>";
        }
        $s .=     '</td>';
        $s .=   '</tr>';
        $s .=   '<tr>';
        $s .=     '<th>Taxonomic Children</th>';
        $s .=     '<td>';
        foreach($this->childtaxa as $child) {
            $s .= "<div><a href='?tsn=$child->tsn'>$child->longname</a></div>";
        }
        $s .=     '</td>';
        $s .=   '</tr>';
        return $s;
    }
}
