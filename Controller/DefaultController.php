<?php

namespace Level42\Bundle\DataTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Level42\Bundle\DataTablesBundle\DataTables\Result\DataTablesResultDoctrine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/datatables")
 */
class DefaultController extends Controller
{
    
    /**
     * @Route("/{entity_class}", name="_datatables_entity")
     * @param DataTablesDoctrine $data
     * @return result
     */
    public function entityAction(DataTablesResultDoctrine $data)
    {
    	$data = $this->get('jms_serializer')->serialize($data, "json");
    	return Response::create($data,200,array('Content-Type'=>"application/json"));
    }    
    
}
