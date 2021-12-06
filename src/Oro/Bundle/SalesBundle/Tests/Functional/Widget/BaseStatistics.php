<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class BaseStatistics extends WebTestCase
{
    /**
     * Request widget configuration form
     */
    protected function getConfigureDialog(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->getWidget()->getId(), '_widgetContainer' => 'dialog']
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting configure widget dialog window!');
    }

    protected function createDateTime(string $modifyStr): \DateTime
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify($modifyStr);

        return $date;
    }

    /**
     * Create fields of 'ItemsView' component
     */
    protected function createMetricsElements(array &$data, string $formName): void
    {
        foreach (array_keys($this->metrics) as $key => $value) {
            $data[$formName]['subWidgets']['items'][] = [
                'id' => $value,
                'order' => $key,
                'show' => 'on',
            ];
        }
    }

    /**
     * Create and set fields of 'WidgetConfigDateRangeFilter' component
     */
    protected function createAndSetDateRangeFormElements(array &$formData, string $formName, array $data = []): void
    {
        $index = 0;
        foreach ($data as $key => $value) {
            if ($index > 0) {
                $formData[$formName]['dateRange']['value'][$key] = $value;
            } else {
                $formData[$formName]['dateRange'][$key] = $value;
            }

            $index++;
        }

        $formData[$formName]['dateRange']['part'] = 'value';
    }

    protected function setAdvancedFilters(array &$data, string $formName, array $advancedFilters): void
    {
        if ($advancedFilters) {
            $filters = json_encode($advancedFilters['filters'], JSON_THROW_ON_ERROR);

            $data[$formName]['queryFilter']['entity'] = $advancedFilters['entity'];
            $data[$formName]['queryFilter']['definition'] = '{"filters":[' . $filters . ']}';
        }
    }

    protected function setComparePrevious(array &$data, string $formName, bool $comparePrevious): void
    {
        if ($comparePrevious) {
            $data[$formName]['usePreviousInterval'] = 1;
        } else {
            unset($data[$formName]['usePreviousInterval']);
        }
    }

    protected function getMetricValueByLabel(string $label): string
    {
        return sprintf('//*[text() = "%s"]/following-sibling::h3[@class="value"]', $label);
    }

    protected function getMetricPreviousIntervalValueByLabel(string $label): string
    {
        return sprintf('//*[text() = "%s"]/following-sibling::div[@class="deviation"][position()=1]/span', $label);
    }

    abstract protected function getWidget(): Widget;
}
