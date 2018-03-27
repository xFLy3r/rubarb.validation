<?php

class Application extends Config {

    private $routingRules = [
        'Application' => [
            'index' => 'Application/actionIndex'
        ],
        'robots.txt' => [
            'index' => 'Application/actionRobots'
        ],
        'debug' => [
            'index' => 'Application/actionDebug'
        ]
    ];

    /**
     * @var $view View
     */
    private $view;

    function __construct() {
        parent::__construct();
        $this->view = new View($this);
        if ($this->requestMethod == 'POST') {
            header('Content-Type: application/json');
            die(json_encode($this->ajaxHandler($_POST)));
        } else {
            //Normal GET request. Nothing to do yet
        }
    }

    public function run() {
        if (array_key_exists($this->routing->controller, $this->routingRules)) {
            if (array_key_exists($this->routing->action, $this->routingRules[$this->routing->controller])) {
                list($controller, $action) = explode(DIRECTORY_SEPARATOR, $this->routingRules[$this->routing->controller][$this->routing->action]);
                call_user_func([$controller, $action]);
            } else { http_response_code(404); die('action not found'); }
        } else { http_response_code(404); die('controller not found'); }
    }



    public function actionIndex() {
        return $this->view->render('index');
    }

    public function actionDebug() {
        return $this->view->render('debug');
    }

    public function actionRobots() {
        return implode(PHP_EOL, ['User-Agent: *', 'Disallow: /']);
    }


    /**
     * Здесь нужно реализовать механизм валидации данных формы
     * @param $data array
     * $data - массив пар ключ-значение, генерируемое JavaScript функцией serializeArray()
     * name - Имя, обязательное поле, не должно содержать цифр и не быть больше 64 символов
     * phone - Телефон, обязательное поле, должно быть в правильном международном формате. Например +38 (067) 123-45-67
     * email - E-mail, необязательное поле, но должно быть либо пустым либо содержать валидный адрес e-mail
     * comment - необязательное поле, но не должно содержать тэгов и быть больше 1024 символов
     *
     * @return array
     * Возвращаем массив с обязательными полями:
     * result => true, если данные валидны, и false если есть хотя бы одна ошибка.
     * error => ассоциативный массив с найдеными ошибками,
     * где ключ - name поля формы, а значение - текст ошибки (напр. ['phone' => 'Некорректный номер']).
     * в случае отсутствия ошибок, возвращать следует пустой массив
     */
    public function actionFormSubmit($data) {
        $errors = [];
        foreach ($data as $key => $value) {
            $userFunc = 'validate' . ucfirst(strtolower($value['name']));

            if (method_exists($this, $userFunc)) {
                if (!call_user_func([$this, $userFunc], $value['value'])) {
                    $key = $value['name'];
                    $errors[$key] = 'Invalid ' . $value['name'];
                    }
                }
            }

        return ['result' => count($errors) === 0, 'error' => $errors];
    }

    /**
     * @param string $name
     * @return bool
     */
    private function validateName(string $name): bool
    {
        $name = trim($name);
        if (strlen($name) > 0 && strlen($name) < 64 && preg_match('/^[A-Za-z\s]+$/', $name)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $phone
     * @return bool
     */
    private function validatePhone(string $phone): bool
    {
        $patterns = ["\+247-\d\d\d\d", "\+376-\d\d\d-\d\d\d", "\+971-5\d-\d\d\d-\d\d\d\d", "\+971-\d-\d\d\d-\d\d\d\d", "\+93-\d\d-\d\d\d-\d\d\d\d", "\+1\(268\)\d\d\d-\d\d\d\d", "\+1\(264\)\d\d\d-\d\d\d\d", "\+355\(\d\d\d\)\d\d\d-\d\d\d", "\+374-\d\d-\d\d\d-\d\d\d", "\+599-\d\d\d-\d\d\d\d", "\+599-\d\d\d-\d\d\d\d", "\+599-9\d\d\d-\d\d\d\d", "\+244\(\d\d\d\)\d\d\d-\d\d\d", "\+672-1\d\d-\d\d\d", "\+54\(\d\d\d\)\d\d\d-\d\d\d\d", "\+1\(684\)\d\d\d-\d\d\d\d", "\+43\(\d\d\d\)\d\d\d-\d\d\d\d", "\+61-\d-\d\d\d\d-\d\d\d\d", "\+297-\d\d\d-\d\d\d\d", "\+994-\d\d-\d\d\d-\d\d-\d\d", "\+387-\d\d-\d\d\d\d\d", "\+387-\d\d-\d\d\d\d", "\+1\(246\)\d\d\d-\d\d\d\d", "\+880-\d\d-\d\d\d-\d\d\d", "\+32\(\d\d\d\)\d\d\d-\d\d\d", "\+226-\d\d-\d\d-\d\d\d\d", "\+359\(\d\d\d\)\d\d\d-\d\d\d", "\+973-\d\d\d\d-\d\d\d\d", "\+257-\d\d-\d\d-\d\d\d\d", "\+229-\d\d-\d\d-\d\d\d\d", "\+1\(441\)\d\d\d-\d\d\d\d", "\+673-\d\d\d-\d\d\d\d", "\+591-\d-\d\d\d-\d\d\d\d", "\+55-\d\d-\d\d\d\d-\d\d\d\d", "\+55-\d\d-\d\d\d\d\d-\d\d\d\d", "\+1\(242\)\d\d\d-\d\d\d\d", "\+975-17-\d\d\d-\d\d\d", "\+975-\d-\d\d\d-\d\d\d", "\+267-\d\d-\d\d\d-\d\d\d", "\+375\(\d\d\)\d\d\d-\d\d-\d\d", "\+501-\d\d\d-\d\d\d\d", "\+243\(\d\d\d\)\d\d\d-\d\d\d", "\+236-\d\d-\d\d-\d\d\d\d", "\+242-\d\d-\d\d\d-\d\d\d\d", "\+41-\d\d-\d\d\d-\d\d\d\d", "\+225-\d\d-\d\d\d-\d\d\d", "\+682-\d\d-\d\d\d", "\+56-\d-\d\d\d\d-\d\d\d\d", "\+237-\d\d\d\d-\d\d\d\d", "\+86\(\d\d\d\)\d\d\d\d-\d\d\d\d", "\+86\(\d\d\d\)\d\d\d\d-\d\d\d", "\+86-\d\d-\d\d\d\d\d-\d\d\d\d\d", "\+57\(\d\d\d\)\d\d\d-\d\d\d\d", "\+506-\d\d\d\d-\d\d\d\d", "\+53-\d-\d\d\d-\d\d\d\d", "\+238\(\d\d\d\)\d\d-\d\d", "\+599-\d\d\d-\d\d\d\d", "\+357-\d\d-\d\d\d-\d\d\d", "\+420\(\d\d\d\)\d\d\d-\d\d\d", "\+49\(\d\d\d\d\)\d\d\d-\d\d\d\d", "\+49\(\d\d\d\)\d\d\d-\d\d\d\d", "\+49\(\d\d\d\)\d\d-\d\d\d\d", "\+49\(\d\d\d\)\d\d-\d\d\d", "\+49\(\d\d\d\)\d\d-\d\d", "\+49-\d\d\d-\d\d\d", "\+253-\d\d-\d\d-\d\d-\d\d", "\+45-\d\d-\d\d-\d\d-\d\d", "\+1\(767\)\d\d\d-\d\d\d\d", "\+1\(809\)\d\d\d-\d\d\d\d", "\+1\(829\)\d\d\d-\d\d\d\d", "\+1\(849\)\d\d\d-\d\d\d\d", "\+213-\d\d-\d\d\d-\d\d\d\d", "\+593-\d\d-\d\d\d-\d\d\d\d", "\+593-\d-\d\d\d-\d\d\d\d", "\+372-\d\d\d\d-\d\d\d\d", "\+372-\d\d\d-\d\d\d\d", "\+20\(\d\d\d\)\d\d\d-\d\d\d\d", "\+291-\d-\d\d\d-\d\d\d", "\+34\(\d\d\d\)\d\d\d-\d\d\d", "\+251-\d\d-\d\d\d-\d\d\d\d", "\+358\(\d\d\d\)\d\d\d-\d\d-\d\d", "\+679-\d\d-\d\d\d\d\d", "\+500-\d\d\d\d\d", "\+691-\d\d\d-\d\d\d\d", "\+298-\d\d\d-\d\d\d", "\+262-\d\d\d\d\d-\d\d\d\d", "\+33\(\d\d\d\)\d\d\d-\d\d\d", "\+508-\d\d-\d\d\d\d", "\+590\(\d\d\d\)\d\d\d-\d\d\d", "\+241-\d-\d\d-\d\d-\d\d", "\+1\(473\)\d\d\d-\d\d\d\d", "\+995\(\d\d\d\)\d\d\d-\d\d\d", "\+594-\d\d\d\d\d-\d\d\d\d", "\+233\(\d\d\d\)\d\d\d-\d\d\d", "\+350-\d\d\d-\d\d\d\d\d", "\+299-\d\d-\d\d-\d\d", "\+220\(\d\d\d\)\d\d-\d\d", "\+224-\d\d-\d\d\d-\d\d\d", "\+240-\d\d-\d\d\d-\d\d\d\d", "\+30\(\d\d\d\)\d\d\d-\d\d\d\d", "\+502-\d-\d\d\d-\d\d\d\d", "\+1\(671\)\d\d\d-\d\d\d\d", "\+245-\d-\d\d\d\d\d\d", "\+592-\d\d\d-\d\d\d\d", "\+852-\d\d\d\d-\d\d\d\d", "\+504-\d\d\d\d-\d\d\d\d", "\+385-\(\d\d\)-\d\d\d-\d\d\d", "\+385-\(\d\d\)-\d\d\d-\d\d\d\d", "\+385-1-\d\d\d\d-\d\d\d", "\+509-\d\d-\d\d-\d\d\d\d", "\+36\(\d\d\d\)\d\d\d-\d\d\d", "\+62\(8\d\d\)\d\d\d-\d\d\d\d", "\+62-\d\d-\d\d\d-\d\d", "\+62-\d\d-\d\d\d-\d\d\d", "\+62-\d\d-\d\d\d-\d\d\d\d", "\+62\(8\d\d\)\d\d\d-\d\d\d", "\+62\(8\d\d\)\d\d\d-\d\d-\d\d\d", "\+353\(\d\d\d\)\d\d\d-\d\d\d", "\+972-5\d-\d\d\d-\d\d\d\d", "\+972-\d-\d\d\d-\d\d\d\d", "\+91\(\d\d\d\d\)\d\d\d-\d\d\d", "\+246-\d\d\d-\d\d\d\d", "\+964\(\d\d\d\)\d\d\d-\d\d\d\d", "\+98\(\d\d\d\)\d\d\d-\d\d\d\d", "\+354-\d\d\d-\d\d\d\d", "\+39\(\d\d\d\)\d\d\d\d-\d\d\d", "\+1\(876\)\d\d\d-\d\d\d\d", "\+962-\d-\d\d\d\d-\d\d\d\d", "\+81-\d\d-\d\d\d\d-\d\d\d\d", "\+81\(\d\d\d\)\d\d\d-\d\d\d", "\+254-\d\d\d-\d\d\d\d\d\d", "\+996\(\d\d\d\)\d\d\d-\d\d\d", "\+855-\d\d-\d\d\d-\d\d\d", "\+686-\d\d-\d\d\d", "\+269-\d\d-\d\d\d\d\d", "\+1\(869\)\d\d\d-\d\d\d\d", "\+850-191-\d\d\d-\d\d\d\d", "\+850-\d\d-\d\d\d-\d\d\d", "\+850-\d\d\d-\d\d\d\d-\d\d\d", "\+850-\d\d\d-\d\d\d", "\+850-\d\d\d\d-\d\d\d\d", "\+850-\d\d\d\d-\d\d\d\d\d\d\d\d\d\d\d\d\d", "\+82-\d\d-\d\d\d-\d\d\d\d", "\+965-\d\d\d\d-\d\d\d\d", "\+1\(345\)\d\d\d-\d\d\d\d", "\+7\(6\d\d\)\d\d\d-\d\d-\d\d", "\+7\(7\d\d\)\d\d\d-\d\d-\d\d", "\+856\(20\d\d\)\d\d\d-\d\d\d", "\+856-\d\d-\d\d\d-\d\d\d", "\+961-\d\d-\d\d\d-\d\d\d", "\+961-\d-\d\d\d-\d\d\d", "\+1\(758\)\d\d\d-\d\d\d\d", "\+423\(\d\d\d\)\d\d\d-\d\d\d\d", "\+94-\d\d-\d\d\d-\d\d\d\d", "\+231-\d\d-\d\d\d-\d\d\d", "\+266-\d-\d\d\d-\d\d\d\d", "\+370\(\d\d\d\)\d\d-\d\d\d", "\+352-\d\d\d-\d\d\d", "\+352-\d\d\d\d-\d\d\d", "\+352-\d\d\d\d\d-\d\d\d", "\+352-\d\d\d\d\d\d-\d\d\d", "\+371-\d\d-\d\d\d-\d\d\d", "\+218-\d\d-\d\d\d-\d\d\d", "\+218-21-\d\d\d-\d\d\d\d", "\+212-\d\d-\d\d\d\d-\d\d\d", "\+377\(\d\d\d\)\d\d\d-\d\d\d", "\+377-\d\d-\d\d\d-\d\d\d", "\+373-\d\d\d\d-\d\d\d\d", "\+382-\d\d-\d\d\d-\d\d\d", "\+261-\d\d-\d\d-\d\d\d\d\d", "\+692-\d\d\d-\d\d\d\d", "\+389-\d\d-\d\d\d-\d\d\d", "\+223-\d\d-\d\d-\d\d\d\d", "\+95-\d\d-\d\d\d-\d\d\d", "\+95-\d-\d\d\d-\d\d\d", "\+95-\d\d\d-\d\d\d", "\+976-\d\d-\d\d-\d\d\d\d", "\+853-\d\d\d\d-\d\d\d\d", "\+1\(670\)\d\d\d-\d\d\d\d", "\+596\(\d\d\d\)\d\d-\d\d-\d\d", "\+222-\d\d-\d\d-\d\d\d\d", "\+1\(664\)\d\d\d-\d\d\d\d", "\+356-\d\d\d\d-\d\d\d\d", "\+230-\d\d\d-\d\d\d\d", "\+960-\d\d\d-\d\d\d\d", "\+265-1-\d\d\d-\d\d\d", "\+265-\d-\d\d\d\d-\d\d\d\d", "\+52\(\d\d\d\)\d\d\d-\d\d\d\d", "\+52-\d\d-\d\d-\d\d\d\d", "\+60-\d\d-\d\d\d-\d\d\d\d", "\+60-11-\d\d\d\d-\d\d\d\d", "\+60\(\d\d\d\)\d\d\d-\d\d\d", "\+60-\d\d-\d\d\d-\d\d\d", "\+60-\d-\d\d\d-\d\d\d", "\+258-\d\d-\d\d\d-\d\d\d", "\+264-\d\d-\d\d\d-\d\d\d\d", "\+687-\d\d-\d\d\d\d", "\+227-\d\d-\d\d-\d\d\d\d", "\+672-3\d\d-\d\d\d", "\+234\(\d\d\d\)\d\d\d-\d\d\d\d", "\+234-\d\d-\d\d\d-\d\d\d", "\+234-\d\d-\d\d\d-\d\d", "\+234\(\d\d\d\)\d\d\d-\d\d\d\d", "\+505-\d\d\d\d-\d\d\d\d", "\+31-\d\d-\d\d\d-\d\d\d\d", "\+47\(\d\d\d\)\d\d-\d\d\d", "\+977-\d\d-\d\d\d-\d\d\d", "\+674-\d\d\d-\d\d\d\d", "\+683-\d\d\d\d", "\+64\(\d\d\d\)\d\d\d-\d\d\d", "\+64-\d\d-\d\d\d-\d\d\d", "\+64\(\d\d\d\)\d\d\d-\d\d\d\d", "\+968-\d\d-\d\d\d-\d\d\d", "\+507-\d\d\d-\d\d\d\d", "\+51\(\d\d\d\)\d\d\d-\d\d\d", "\+689-\d\d-\d\d-\d\d", "\+675\(\d\d\d\)\d\d-\d\d\d", "\+63\(\d\d\d\)\d\d\d-\d\d\d\d", "\+92\(\d\d\d\)\d\d\d-\d\d\d\d", "\+48\(\d\d\d\)\d\d\d-\d\d\d", "\+970-\d\d-\d\d\d-\d\d\d\d", "\+351-\d\d-\d\d\d-\d\d\d\d", "\+680-\d\d\d-\d\d\d\d", "\+595\(\d\d\d\)\d\d\d-\d\d\d", "\+974-\d\d\d\d-\d\d\d\d", "\+262-\d\d\d\d\d-\d\d\d\d", "\+40-\d\d-\d\d\d-\d\d\d\d", "\+381-\d\d-\d\d\d-\d\d\d\d", "\+7\(\d\d\d\)\d\d\d-\d\d-\d\d", "\+250\(\d\d\d\)\d\d\d-\d\d\d", "\+966-5-\d\d\d\d-\d\d\d\d", "\+966-\d-\d\d\d-\d\d\d\d", "\+677-\d\d\d-\d\d\d\d", "\+677-\d\d\d\d\d", "\+248-\d-\d\d\d-\d\d\d", "\+249-\d\d-\d\d\d-\d\d\d\d", "\+46-\d\d-\d\d\d-\d\d\d\d", "\+65-\d\d\d\d-\d\d\d\d", "\+290-\d\d\d\d", "\+290-\d\d\d\d", "\+386-\d\d-\d\d\d-\d\d\d", "\+421\(\d\d\d\)\d\d\d-\d\d\d", "\+232-\d\d-\d\d\d\d\d\d", "\+378-\d\d\d\d-\d\d\d\d\d\d", "\+221-\d\d-\d\d\d-\d\d\d\d", "\+252-\d\d-\d\d\d-\d\d\d", "\+252-\d-\d\d\d-\d\d\d", "\+252-\d-\d\d\d-\d\d\d", "\+597-\d\d\d-\d\d\d\d", "\+597-\d\d\d-\d\d\d", "\+211-\d\d-\d\d\d-\d\d\d\d", "\+239-\d\d-\d\d\d\d\d", "\+503-\d\d-\d\d-\d\d\d\d", "\+1\(721\)\d\d\d-\d\d\d\d", "\+963-\d\d-\d\d\d\d-\d\d\d", "\+268-\d\d-\d\d-\d\d\d\d", "\+1\(649\)\d\d\d-\d\d\d\d", "\+235-\d\d-\d\d-\d\d-\d\d", "\+228-\d\d-\d\d\d-\d\d\d", "\+66-\d\d-\d\d\d-\d\d\d\d", "\+66-\d\d-\d\d\d-\d\d\d", "\+992-\d\d-\d\d\d-\d\d\d\d", "\+690-\d\d\d\d", "\+670-\d\d\d-\d\d\d\d", "\+670-77\d-\d\d\d\d\d", "\+670-78\d-\d\d\d\d\d", "\+993-\d-\d\d\d-\d\d\d\d", "\+216-\d\d-\d\d\d-\d\d\d", "\+676-\d\d\d\d\d", "\+90\(\d\d\d\)\d\d\d-\d\d\d\d", "\+1\(868\)\d\d\d-\d\d\d\d", "\+688-90\d\d\d\d", "\+688-2\d\d\d\d", "\+886-\d-\d\d\d\d-\d\d\d\d", "\+886-\d\d\d\d-\d\d\d\d", "\+255-\d\d-\d\d\d-\d\d\d\d", "\+380\(\d\d\)\d\d\d-\d\d-\d\d", "\+256\(\d\d\d\)\d\d\d-\d\d\d", "\+44-\d\d-\d\d\d\d-\d\d\d\d", "\+598-\d-\d\d\d-\d\d-\d\d", "\+998-\d\d-\d\d\d-\d\d\d\d", "\+39-6-698-\d\d\d\d\d", "\+1\(784\)\d\d\d-\d\d\d\d", "\+58\(\d\d\d\)\d\d\d-\d\d\d\d", "\+1\(284\)\d\d\d-\d\d\d\d", "\+1\(340\)\d\d\d-\d\d\d\d", "\+84-\d\d-\d\d\d\d-\d\d\d", "\+84\(\d\d\d\)\d\d\d\d-\d\d\d", "\+678-\d\d-\d\d\d\d\d", "\+678-\d\d\d\d\d", "\+681-\d\d-\d\d\d\d", "\+685-\d\d-\d\d\d\d", "\+967-\d\d\d-\d\d\d-\d\d\d", "\+967-\d-\d\d\d-\d\d\d", "\+967-\d\d-\d\d\d-\d\d\d", "\+27-\d\d-\d\d\d-\d\d\d\d", "\+260-\d\d-\d\d\d-\d\d\d\d", "\+263-\d-\d\d\d\d\d\d", "\+1\(\d\d\d\)\d\d\d-\d\d\d\d"];
        foreach ($patterns as $pattern) {
            if (preg_match('/^' . $pattern .'$/', $phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function validateEmail(string $email): bool
    {
        $email = trim($email);
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $comment
     * @return bool
     */
    private function validateComment(string $comment): bool
    {
        $comment = trim($comment);
        if ($comment) {
            if ($comment != strip_tags($comment) || strlen($comment) > 1024) {
                return false;
            }
        }

        return true;
    }

    /**
     * Функция обработки AJAX запросов
     * @param $post
     * @return array
     */
    private function ajaxHandler($post) {
        if (count($post)) {
            if (isset($post['method'])) {
                switch($post['method']) {
                    case 'formSubmit': $result = $this->actionFormSubmit($post['data']);
                        break;


                    default: $result = ['error' => 'Unknown method']; break;
                }
            } else { $result = ['error' => 'Unspecified method!']; }
        } else { $result = ['error' => 'Empty request!']; }
        return $result;
    }
}
