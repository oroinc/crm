<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes;

use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;

/**
 * The aim of this service is map fields from orderEntity to orderNoteEntity
 * and sanitize value in field "Message"
 */
class NoteFieldsProcessor implements ProcessorInterface
{
    /**
     * @var HtmlTagHelper
     */
    private $htmlTagHelper;

    /**
     * @param HtmlTagHelper $htmlTagHelper
     */
    public function __construct(HtmlTagHelper $htmlTagHelper)
    {
        $this->htmlTagHelper = $htmlTagHelper;
    }

    /**
     * @param Context $context
     */
    public function process(Context $context)
    {
        $order = $context->getOrder();
        $orderNote = $context->getOrderNote();

        $orderNote->setOwner($order->getOwner());
        $orderNote->setOrganization($order->getOrganization());
        $orderNote->setMessage(
            $this->htmlTagHelper->sanitize($orderNote->getMessage())
        );

        return;
    }
}
