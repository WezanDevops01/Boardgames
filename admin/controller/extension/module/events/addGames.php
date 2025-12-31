<?php
class ControllerExtensionModuleEventsAddGames extends Controller {
	private $error = array();

	public function index() {
    // Load language and set document title
    $this->language->load('extension/module/addGames');
    $this->document->setTitle($this->language->get('heading_title'));

    // Set breadcrumbs
    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
        'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
        'separator' => false
    );

    $data['breadcrumbs'][] = array(
        'text'      => $this->language->get('heading_title'),
        'href'      => $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true),
        'separator' => ' :: '
    );

    // Set form action and cancel URLs
    $data['action'] = $this->url->link('extension/module/events/addGames', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true);
    
    $data['user_token'] = $this->session->data['user_token'];


    // Load our new model for game management
    $this->load->model('extension/events/addGames');

    // Process form submission
    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      
        if (isset($this->request->post['product_id'])) {
            // Retrieve arrays from POST data
            $product_ids    = $this->request->post['product_id'];
            $product_names  = $this->request->post['product_name'];
            $registrations  = $this->request->post['registrations'];
            $lengths        = $this->request->post['length'];
            $game_types     = $this->request->post['game_type'];
            $difficulties   = $this->request->post['difficulty'];
            $product_videos = $this->request->post['product_video'];
            
           

            $games = array();
            // Prepare data for each game submitted
            for ($i = 0; $i < count($product_ids); $i++) {
                $games[] = array(
                    'product_id'    => $product_ids[$i],
                    'product_name'  => $product_names[$i],
                    'registrations' => $registrations[$i],
                    'length'        => $lengths[$i],
                    'game_type'     => $game_types[$i],
                    'difficulty'    => $difficulties[$i],
                    'product_video' => $product_videos[$i]
                );
            }

            // Insert new game records, skipping empty or duplicate product IDs
            $this->model_extension_events_addGames->addGames($games);

            // Set success message and redirect if needed
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    // Retrieve existing games from the database to show in the table
    $data['games'] = $this->model_extension_events_addGames->getGames();

    // Render the view with header, column left, and footer
    $data['header']      = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer']      = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/events/addGames', $data));
}

    public function getProductInfo() {
    $this->load->language('extension/module/events');
    $json = array();

    if (isset($this->request->post['product_ids']) && is_array($this->request->post['product_ids'])) {
        $product_ids = array_map('intval', $this->request->post['product_ids']);

        if (!empty($product_ids)) {
            $this->load->model('extension/events/events');

            foreach ($product_ids as $product_id) {
                $product_info = $this->model_extension_events_events->getProductInfo($product_id);
               
                 $this->load->model('extension/events/addGames');
                $video = $this->model_extension_events_addGames->getProductVideo($product_id);
                
                // Fetch attributes and process difficulty level
                $difficulty = $this->model_extension_events_events->getProductAttribute($product_id, 510);

                if ($difficulty !== null) {
                    if ($difficulty >= 0 && $difficulty <= 1.5) {
                        $difficultyText = "Easy";
                    } elseif ($difficulty >= 1.6 && $difficulty <= 3.5) {
                        $difficultyText = "Medium";
                    } elseif ($difficulty >= 3.6 && $difficulty <= 4) {
                        $difficultyText = "Difficult";
                    } elseif ($difficulty > 4) {
                        $difficultyText = "Very Difficult";
                    }
                } else {
                    $difficultyText = "0";
                }

                if ($product_info) {
                    $attributes = array(
                        'player' => $this->model_extension_events_events->getProductAttribute($product_id, 501),
                        'Length' => $this->model_extension_events_events->getProductAttribute($product_id, 503),
                        'GameType' => $this->model_extension_events_events->getProductAttribute($product_id, 509),
                        'Difficulty' => $difficultyText,
                        'Video' => $video['video']
                    );

                    // Ensure all values are non-null to avoid issues
                    $attributes = array_map(function ($value) {
                        return $value !== null ? $value : '';
                    }, $attributes);

                    // Merge product info and attributes into response array
                    $json[] = array_merge($product_info, $attributes);
                } else {
                    $json[] = array('error' => $this->language->get('error_product_not_found'));
                }
            }
        } else {
            $json['error'] = "No new product IDs found.";
        }
    } else {
        $json['error'] = $this->language->get('error_missing_product_ids');
    }

    // Set JSON response header
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}


       public function addGames() {
        $this->load->language('extension/module/events'); // Load language file
        
        $json = array(); // Initialize response array

        // Check if the request is POST
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $postData = $this->request->post; // Get form data

            // Log or debug received data
            error_log(print_r($postData, true)); // Save to log file (optional)
            //echo "<pre>"; print_r($postData); echo "</pre>"; // Debugging purpose

            // Store data in the response JSON
            $json['data'] = $postData;
            $json['success'] = "Form submitted successfully!";
        } else {
            $json['error'] = "Invalid request!";
        }

        // Set response headers
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
