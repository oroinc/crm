<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\DashboardBundle\Entity\Widget;

abstract class BaseStatistics extends WebTestCase
{
    /**
     * Request widget configuration form
     */
    protected function getConfigureDialog()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->getWidget()->getId(), '_widgetContainer' => 'dialog']
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting configure widget dialog window !');
    }

    /**
     * @param string $modifyStr
     * @return \DateTime
     */
    protected function createDateTime($modifyStr)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify($modifyStr);

        return $date;
    }

    /**
     * Create fields of 'ItemsView' component
     *
     * @param Form $form
     */
    protected function createMetricsElements(Form $form)
    {
        $fieldName = array_keys($form->all())[0];
        $formName = substr($fieldName, 0, strpos($fieldName, '['));

        $doc = new \DOMDocument("1.0");
        $metricsKeys = array_keys($this->metrics);
        $metricsCount = count($metricsKeys);
        $html = '';
        for ($index=0; $index < $metricsCount; $index++) {
            $html .= sprintf(
                '<input type="text" name="'.$formName.'[subWidgets][items][%1$s][id]" value="%2$s" />' .
                '<input type="text" name="'.$formName.'[subWidgets][items][%1$s][order]" value="%1$s" />' .
                '<input type="checkbox" name="'.$formName.'[subWidgets][items][%1$s][show]" checked />',
                $index,
                $metricsKeys[$index]
            );
        }

        $doc->loadHTML($html);

        for ($index=0; $index < $metricsCount; $index++) {
            $subItemTypeField = new InputFormField($doc->getElementsByTagName('input')->item(0 + $index * 3));
            $form->set($subItemTypeField);
            $subItemTypeField = new InputFormField($doc->getElementsByTagName('input')->item(1 + $index * 3));
            $form->set($subItemTypeField);
            $subItemTypeField = new ChoiceFormField($doc->getElementsByTagName('input')->item(2 + $index * 3));
            $form->set($subItemTypeField);
        }
    }

    /**
     * Create and set fields of 'WidgetConfigDateRangeFilter' component
     *
     * @param Form $form
     * @param array|null $data
     */
    protected function createAndSetDateRangeFormElements(Form $form, $data = null)
    {
        $fieldName = array_keys($form->all())[0];
        $formName = substr($fieldName, 0, strpos($fieldName, '['));

        $doc = new \DOMDocument("1.0");
        $inputs = '<input type="text" name="'.$formName.'[dateRange][type]" value="" />';

        if (!empty($data) && count($data) > 1) {
            $inputs .= '<input type="text" name="'.$formName.'[dateRange][value][start]" value="" />' .
                '<input type="text" name="'.$formName.'[dateRange][value][end]" value="" />';
        }
        $doc->loadHTML($inputs);

        $index = 0;
        foreach ($data as $key => $value) {
            $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item($index));
            $form->set($dateRangeTypeField);

            $fieldName = $index > 0
                ? $formName.'[dateRange][value]['.$key.']'
                : $formName.'[dateRange]['.$key.']' ;

            $form[$fieldName] = $value;
            $index++;
        }

        $form[$formName.'[dateRange][part]'] = 'value';
    }

    /**
     * @param Form $form
     * @param array $advancedFilters
     */
    protected function setAdvancedFilters(Form $form, array $advancedFilters)
    {
        $fieldName = array_keys($form->all())[0];
        $formName = substr($fieldName, 0, strpos($fieldName, '['));

        if (!empty($advancedFilters)) {
            $filters = json_encode($advancedFilters['filters']);
            $form[$formName.'[queryFilter][entity]'] = $advancedFilters['entity'];
            $form[$formName.'[queryFilter][definition]'] = '{"filters":['.$filters.']}';
        }
    }

    /**
     * @param Form $form
     * @param bool $comparePrevious
     */
    protected function setComparePrevious(Form $form, $comparePrevious)
    {
        $fieldName = array_keys($form->all())[0];
        $formName = substr($fieldName, 0, strpos($fieldName, '['));

        $form->remove($formName . '[usePreviousInterval]');
        if ($comparePrevious) {
            $doc = new \DOMDocument("1.0");
            $doc->loadHTML(
                '<input type="text" name="' . $formName . '[usePreviousInterval]" value="1" checked="checked"/>'
            );
            $compareToPreviousField = new InputFormField($doc->getElementsByTagName('input')->item(0));
            $form->set($compareToPreviousField);
            $form[$formName.'[usePreviousInterval]'] = 1;
        }
    }


    /**
     * @param string $label
     *
     * @return string
     */
    protected function getMetricValueByLabel($label)
    {
        return sprintf('//*[text() = "%s"]/following-sibling::h3[@class="value"]', $label);
    }
    
    /**
     * @param string $label
     *
     * @return string
     */
    protected function getMetricPreviousIntervalValueByLabel($label)
    {
        return sprintf('//*[text() = "%s"]/following-sibling::div[@class="deviation"][position()=1]/span', $label);
    }

    /**
     * @return Widget
     */
    abstract protected function getWidget();
}
