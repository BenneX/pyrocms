<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller
{

    function __construct()
    {
        parent::Admin_Controller();
        $this->load->module_model('services', 'services_m');
        
        $this->data->pay_per_options = array(
        	'' 		=>	'one off', 
			'hour'	=>	'per Hour',
			'day'	=>	'per Day',
			'week'	=>	'per Week',
			'month'	=>	'per Month',
			'year'	=>	'per Year'
       	);
    }

    function index()
    {
        $this->load->helper('text');
		
        // Create pagination links
    	$total_rows = $this->services_m->countServices();
    	$this->data->pagination = create_pagination('admin/suppliers/index', $total_rows);

    	// Using this data, get the relevant results
    	$this->data->services = $this->services_m->getServices(array('limit' => $this->data->pagination['limit']));

    	$this->layout->create('admin/index', $this->data);
    }
    
    // Admin: Create a New Service
    function create()
    {
        $this->load->library('validation');
        $rules['title'] = 'trim|required|callback__createTitleCheck';
        $rules['description'] = 'trim|required';
        $rules['price'] = 'trim|is_numeric';
        $rules['pay_per'] = 'trim';
        $this->validation->set_rules($rules);
        $this->validation->set_fields();
        
        
        if ($this->validation->run())
        {
            if ($this->services_m->newService($_POST))
            {
                $this->session->set_flashdata('success', 'The service "'.$this->input->post('title').'" was added.');
            } 
            
            else
            {
                $this->session->set_flashdata('error', 'An error occured.');
            }
            
            redirect('admin/services/index');
        }
        
        foreach(array_keys($rules) as $field)
		{
        	$this->data->service->$field = (isset($_POST[$field])) ? $this->validation->$field : '';
        }
    	
    	// Load WYSIWYG editor
		$this->layout->extra_head( $this->load->view('fragments/wysiwyg', $this->data, TRUE) );
		
        $this->layout->create('admin/form', $this->data);
    }
    
    // Admin: Edit a Service
    function edit($slug = '')
    {
    	// No service to edit
        if (!$slug) redirect('admin/services/index');
            
        $this->load->library('validation');
        $rules['title'] = 'trim|required';
        $rules['description'] = 'trim|required';
        $rules['price'] = 'trim|is_numeric';
        $rules['pay_per'] = 'trim';
        $this->validation->set_rules($rules);
        $this->validation->set_fields();

        $this->data->service = $this->services_m->getService($slug);

        // Override the database value for each populated field
        foreach(array_keys($rules) as $field) {
        	if(isset($_POST[$field]))
        	$this->data->service->$field = $this->validation->$field;
        }

        if ($this->validation->run()) {
        	if ($this->services_m->updateService($_POST, $slug)) {
        		$this->session->set_flashdata(array('success'=>'The service "'.$this->input->post('title').'" was saved.'));
        		redirect('admin/services/index', $this->data);

        	} else {
        		$this->session->set_flashdata(array('error'=>'An error occurred.'));
        	}
        }
    	
    	// Load WYSIWYG editor
		$this->layout->extra_head( $this->load->view('fragments/wysiwyg', $this->data, TRUE) );
		
        $this->layout->create('admin/form', $this->data);
    }
    
    // Admin: Delete a Service
    function delete($id = 0)
    {
		// An ID was passed in the URL, lets delete that
		$ids_array = ($id > 0) ? array($id) : $this->input->post('action_to');
		
		if(empty($ids_array))
    	{
			$this->session->set_flashdata('error', 'You need to select one or more services to delete.');
			redirect('admin/services/index');
		}
	
        foreach ($ids_array as $id)
		{
			$this->services_m->deleteService($id);
		}

        $this->session->set_flashdata('success', 'The service(s) were deleted.');
		redirect('admin/services/index');
    }
    
    // Callback: from create()
    function _createTitleCheck($title = '') {
        if ($this->services_m->checkTitle($title)) {
            $this->validation->set_message('_createTitleCheck', 'A service with the name "'.$title.'" already exists.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

}

?>