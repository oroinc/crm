<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\ConnectionException;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateReminderEmailTemplates extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Update date format in reminder email templates using recipient organization localization settings';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $dateFilterPattern = "|date('F j, Y, g:i A')";
        $calendarRangePattern[] = 'calendar_date_range(entity.start, entity.end, entity.allDay, 1)';
        $calendarRangePattern[] = "calendar_date_range(entity.start, entity.end, entity.allDay, 'F j, Y', 1)";
        $dateFilterReplacement = "|oro_format_datetime_organization({'organization': entity.organization})";
        $calendarRangeReplacement = 'calendar_date_range_organization(entity.start,' .
            ' entity.end, entity.allDay, 1, null, null, null, entity.organization)';
        $this->updateReminderTemplates(
            $logger,
            'task_reminder',
            $dateFilterPattern,
            $dateFilterReplacement,
            $calendarRangePattern,
            $calendarRangeReplacement
        );
    }

    /**
     * @param LoggerInterface $logger
     * @param string          $templateName
     * @param string|array    $dateFilterPattern
     * @param string          $dateFilterReplacement
     * @param string|array    $calendarRangePattern
     * @param string          $calendarRangeReplacement
     *
     * @throws ConnectionException
     * @throws \Exception
     */
    protected function updateReminderTemplates(
        LoggerInterface $logger,
        $templateName,
        $dateFilterPattern,
        $dateFilterReplacement,
        $calendarRangePattern,
        $calendarRangeReplacement
    ) {
        $sql = 'SELECT * FROM oro_email_template WHERE name = :name ORDER BY id';
        $parameters = ['name' => $templateName];
        $types = ['name' => 'string'];

        $this->logQuery($logger, $sql, $parameters, $types);
        $templates = $this->connection->fetchAll($sql, $parameters, $types);

        try {
            $this->connection->beginTransaction();
            foreach ($templates as $template) {
                $subject = str_replace($dateFilterPattern, $dateFilterReplacement, $template['subject']);
                $content = str_replace($dateFilterPattern, $dateFilterReplacement, $template['content']);
                $content = str_replace($calendarRangePattern, $calendarRangeReplacement, $content);
                $this->connection->update(
                    'oro_email_template',
                    ['subject' => $subject, 'content' => $content],
                    ['id' => $template['id']]
                );
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
