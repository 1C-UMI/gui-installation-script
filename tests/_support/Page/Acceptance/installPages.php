<?php

    namespace Page\Acceptance;

    /**
     * Содержит основные методы для установки umi.cms2 и xpath.
    */
    class installPages {
        public $URL;
        public $labelTypeOfSite;
        public $keyField = ("//input[@name=\"key\"]");
        public $nextButton = "//input[@value=\"Далее   >\"]";
        public $templateNextButton = "//input[@value=\"Далее   »\"]";
        public $dbHostField = "//input[@name=\"host\"]";
        public $dbNameField = "//input[@name=\"dbname\"]";
        public $dbUserField = "//input[@name=\"user\"]";
        public $dbPasswordField = "//input[@name=\"password\"]";
        public $backupCheckbox = "//label[@for=\"cbbackup\"]";
        public $chooseTemplateHeader = "//p[@class=\"check_user\"]";
        public $showLogs = "//a[@class=\"wrapper\"]";
        public $loginField = "//input[@name=\"sv_login\"]";
        public $emailField = "//input[@name=\"sv_email\"]";
        public $passwordField = "//input[@name=\"sv_password\"]";
        public $verifyPasswordField = "//input[@name=\"sv_password2\"]";
        public $systemInstalledText = "//p[text()=\"Установка системы завершена\"]";
        public $typeOfSiteSearchField = "//input[@class=\"search\"]";
        public $typeOfSiteSearchButton = "//input[@class=\"next_step_submit\"]";
        public $typeOfSite1Span = "//label[@for=\"type_of_site1\"]/span";
        public $searchResultOfSite = "//div[@class=\"site\"]";

        protected $acceptanceTester;
        protected $installIni;

        public function __construct(\AcceptanceTester $acceptanceTester, $installIni) {
            $this->acceptanceTester = $acceptanceTester;
            $this->installIni = $installIni;
            $this->URL = "http://$installIni->domain/install.php";
        }

        /** Формирует xpath адрес для полученного типа решения
         * @param $type - тип решения, по умолчанию тип 1
         * @return string
        */
        public function getTypeOfSiteSpanXpath($type = 1) {
            return "//label[@for=\"type_of_site$type\"]/span";
        }

        /** Данный метод отвечает за выбор в браузере типа шаблона.
         * @param $type - передается тип готового решения
         */
        private function selectTypeOfSite($type) {
            $this->acceptanceTester->click($this->getTypeOfSiteSpanXpath($type));
            $this->acceptanceTester->click($this->templateNextButton);
        }

        /**
         * Данный метод используется для заполнения форм:
         * 1)Ключ
         * 2)БД
         * Так же ставит чекбокс подтверждения наличия бэкапа.
        */
        public function coreInstaller() {
            $acceptanceTester = $this->acceptanceTester;
            $installIni = $this->installIni;
            $acceptanceTester->fillField($this->keyField, $installIni->key);
            $acceptanceTester->click($this->nextButton);
            $acceptanceTester->waitForElementVisible($this->dbHostField);
            $acceptanceTester->fillField($this->dbHostField, $installIni->host);
            $acceptanceTester->fillField($this->dbNameField, $installIni->dbname);
            $acceptanceTester->fillField($this->dbUserField, $installIni->dbuser);
            $acceptanceTester->fillField($this->dbPasswordField, $installIni->dbpassword);
            $acceptanceTester->click($this->nextButton);
            $acceptanceTester->waitForElementVisible($this->backupCheckbox);
            $acceptanceTester->click($this->backupCheckbox);
            $acceptanceTester->click($this->nextButton);
            $acceptanceTester->waitForElementVisible($this->showLogs);
            $acceptanceTester->click($this->showLogs);
        }

        /** Находит через поиск введенное решение и устанавливает его.
         * На данный момент используется только для 1 и 2 типов решения
         * @throws \Exception - если установка не удалась.
        */
        private function searchTemplateInstaller() {
            $acceptanceTester = $this->acceptanceTester;
            $templateName = $this->installIni->templateName;
            $chooseButton = "//div/a[@rel=\"$templateName\"]";
            $acceptanceTester->click($this->templateNextButton);
            $acceptanceTester->waitForElementVisible($this->typeOfSiteSearchField);
            $acceptanceTester->fillField($this->typeOfSiteSearchField, $templateName);
            $acceptanceTester->click($this->typeOfSiteSearchButton);
            $acceptanceTester->waitForElementVisible($this->searchResultOfSite);
            $acceptanceTester->moveMouseOver($this->searchResultOfSite . "/div");
            $acceptanceTester->waitForElementVisible($chooseButton);
            $acceptanceTester->click($chooseButton);
            $acceptanceTester->click($this->templateNextButton);
            $acceptanceTester->click($this->templateNextButton);
        }

        /**
         * Метод выбирает устанавливаемый шаблон.
         * @param $type - передается тип готового решения
         * @throws \Exception - если установка не удалась
        */
        public function templateInstaller($type) {
            $acceptanceTester = $this->acceptanceTester;
            $templateName = $this->installIni->templateName;
            $this->labelTypeOfSite = "//label[@for=\"$templateName\"]/span";
            switch ($type) {
                case "demo":
                {
                    $this->selectTypeOfSite(3);
                    $acceptanceTester->waitForElementVisible($this->labelTypeOfSite);
                    $acceptanceTester->click($this->labelTypeOfSite);
                    $acceptanceTester->click($this->templateNextButton);
                    break;
                }
                case "free":
                    $this->selectTypeOfSite(2);
                    $this->searchTemplateInstaller();
                    break;
                case "paid":
                    $this->selectTypeOfSite(3);
                    $this->searchTemplateInstaller();
                    break;
                default:
                {
                    if ($templateName != "_blank") {
                        echo("\nНе найден шаблон с именем $templateName.\n"
                            . "Проверьте название в файле install.ini.\n"
                            . "Если название корректное, то убедитесь, что этот шаблон привязан к вашему ключу.\n"
                            . "Устанавливаю без шаблона.\n\n");
                    }
                    $this->selectTypeOfSite(4);
                    break;
                }
            }
        }

        /**
         * Отвечает за заполнение формы администратора
        */
        public function svUserInstaller() {
            $acceptanceTester = $this->acceptanceTester;
            $installIni = $this->installIni;
            $acceptanceTester->fillField($this->emailField, $installIni->email);
            $acceptanceTester->fillField($this->loginField, $installIni->login);
            $acceptanceTester->fillField($this->passwordField, $installIni->password);
            $acceptanceTester->fillField($this->verifyPasswordField, $installIni->password);
            $acceptanceTester->click($this->templateNextButton);
        }

}
