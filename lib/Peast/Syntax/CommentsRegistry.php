<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax;

/**
 * Comments registry class. Internal class used to manage comments
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class CommentsRegistry
{
    /**
     * Scanner
     * 
     * @var Scanner 
     */
    protected $scanner;
    
    /**
     * Map of the indices where nodes start
     * 
     * @var int 
     */
    protected $nodesStartMap = array();
    
    /**
     * Map of the indices where nodes end
     * 
     * @var int 
     */
    protected $nodesEndMap = array();
    
    /**
     * Class constructor
     * 
     * @param Parser    $parser     Parser
     * @param Scanner   $scanner    Scanner
     */
    public function __construct(Parser $parser, Scanner $scanner)
    {
        $parser->getEventsEmitter()
               ->addListener("NodeCompleted", array($this, "onNodeCompleted"))
               ->addListener("EndParsing", array($this, "onEndParsing"));
        
        //Force token registration on scanner
        $this->scanner = $scanner->enableTokenRegistration(true);
    }
    
    /**
     * Listener called every time a node is completed by the parser
     * 
     * @param Node\Node   $node     Completed node
     * 
     * @return void
     */
    public function onNodeCompleted(Node\Node $node)
    {
        //Every time a node is completed, register its start and end indices
        //in the relative properties
        $loc = $node->getLocation();
        foreach (array("Start", "End") as $pos) {
            $val = $loc->{"get$pos"}()->getIndex();
            $prop = "nodes{$pos}Map";
            if (!isset($this->$prop[$val])) {
                $this->$prop[$val] = array();
            }
            $this->$prop[$val][] = $node;
        }
    }
    
    /**
     * Listener called when parsing process ends
     * 
     * @return void
     */
    public function onEndParsing()
    {
        //Make sure nodes start indices map is sorted
        ksort($this->nodesStartMap);
        
        //Loop all registered tokens
        $group = null;
        $comments = array();
        $tokens = $this->scanner->getTokens();
        
        foreach ($tokens as $k => $token) {
            //Group adjacent comments
            if ($token->getType() === Token::TYPE_COMMENT) {
                
                if (!$group) {
                    $prev = $k ?
                            $tokens[$k - 1]->getLocation()->getEnd()->getIndex() :
                            null;
                    
                    //Create the comments group and store informations abuout
                    //the indices from previous and next tokens
                    $group = array(
                        "prev" => $prev,
                        "next" => null,
                        "comments" => array(),
                    );
                }
                
                //Add the comment to the current comments group
                $group["comments"][] = $token;
                
            } else {
                
                //The first non-comment token closes the comments group
                if ($group) {
                    $group["next"] = $token->getLocation()->getStart()->getIndex();
                    $this->findNodeForCommentsGroup($group);
                }
                $group = false;
            }
        }
        
        // At the end if there is an open comment group, analyze it
        if ($group) {
            $this->findNodeForCommentsGroup($group);
        }
    }
    
    /**
     * Finds the node to attach the given comments group
     * 
     * @param array    $comments   Comments group
     * 
     * @return void
     */
    public function findNodeForCommentsGroup($group)
    {
        $next = $group["next"];
        $prev = $group["prev"];
        $comments = $group["comments"];
        $leading = true;
        
        //If the group of comments has a next token index that appears
        //in the map of start node indices, add the group to the
        //corresponding node's leading comments. This associates
        //comments that appear immediately before a node.
        //For example: /*comment*/ for (;;){}
        if (isset($this->nodesStartMap[$next])) {
            $nodes = $this->nodesStartMap[$next];
        }
        //If the group of comments has a previous token index that appears
        //in the map of end node indices, add the group to the
        //corresponding node's trailing comments. This associates
        //comments that appear immediately after a node.
        //For example: for (;;){} /*comment*/ 
        elseif (isset($this->nodesEndMap[$prev])) {
            $nodes = $this->nodesEndMap[$prev];
            $leading = false;
        }
        //Otherwise, find a node that wraps the comments position.
        //This associates inner comments:
        //For example: for /*comment*/ (;;){}
        else {
            //Calculate comments group boundaries
            $start = $comments[0]->getLocation()->getStart()->getIndex();
            $end = $comments[count($comments) -1]->getLocation()->getEnd()->getIndex();
            $nodes = array();
            
            //Loop all the entries in the start index map
            foreach ($this->nodesStartMap as $idx => $ns) {
                //If the index is higher than the start index of the comments
                //group, stop
                if ($idx > $start) {
                    break;
                }
                foreach ($ns as $node) {
                    //Check if the comments group is inside node indices range
                    if ($node->getLocation()->getEnd()->getIndex() >= $end) {
                        $nodes[] = $node;
                    }
                }
            }
        }
        
        //If there are multiple possible nodes to associate the comments to,
        //find the shortest one
        if (count($nodes) > 1) {
            usort($nodes, array($this, "compareNodesLength"));
        }
        $this->associateComments($nodes[0], $comments, $leading);
    }
    
    /**
     * Compares node length 
     * 
     * @param Node\Node  $node1     First node
     * @param Node\Node  $node2     Second node
     * 
     * @return int
     */
    public function compareNodesLength($node1, $node2)
    {
        $loc1 = $node1->getLocation();
        $length1 = $loc1->getEnd()->getIndex() - $loc1->getStart()->getIndex();
        $loc2 = $node2->getLocation();
        $length2 = $loc2->getEnd()->getIndex() - $loc2->getStart()->getIndex();
        //If the nodes have the same length make sure to choose nodes
        //different from Program nodes
        if ($length1 === $length2) {
            if ($node1 instanceof Node\Program) {
                $length1 += 1000;
            } elseif ($node2 instanceof Node\Program) {
                $length2 += 1000;
            }
        }
        return $length1 < $length2 ? -1 : 1;
    }
    
    /**
     * Adds comments to the given node
     * 
     * @param Node\Node     $node       Node
     * @param array         $comments   Array of comments to add
     * @param bool          $leading    True to add comments as leading comments
     *                                  or false to add them as trailing comments
     * 
     * @return void
     */
    public function associateComments($node, $comments, $leading)
    {
        $fn = ($leading ? "Leading" : "Trailing") . "Comments";
        $currentComments = $node->{"get$fn"}();
        foreach ($comments as $comment) {
            $loc = $comment->getLocation();
            $commentNode = new Node\Comment;
            $commentNode->setStartPosition($loc->getStart())
                        ->setEndPosition($loc->getEnd())
                        ->setRawText($comment->getValue());
            $currentComments[] = $commentNode;
        }
        $node->{"set$fn"}($currentComments);
    }
}