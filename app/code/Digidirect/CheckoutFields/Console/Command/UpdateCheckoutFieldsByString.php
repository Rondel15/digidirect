<?php
namespace Digidirect\CheckoutFields\Console\Command;

use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;
use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Digidirect\CheckoutFields\Model\Component\Type\AbstractType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCheckoutFieldsByString
 *
 * @package Digidirect\CheckoutFields\Console\Command
 */
class UpdateCheckoutFieldsByString extends Command
{
    /**
     * Field to check before fill update field
     */
    const CHECK_FIELD_ARGUMENT = 'check_field';

    /**
     * Field which is needed to update by string
     */
    const UPDATE_FIELD_ARGUMENT = 'update_field';

    /**
     * Value argument
     */
    const VALUE_ARGUMENT = 'value';

    /**
     * Object argument
     */
    const OBJECT_ARGUMENT = 'object';

    /**
     * Objects
     */
    const OBJECT_QUOTE = 'quote';
    const OBJECT_ORDER = 'order';

    /**
     * Object flag
     */
    const OBJECT_QUOTE_FLAG = 1;
    const OBJECT_ORDER_FLAG = 2;

    /**
     * Valid objects
     */
    const OBJECTS_FLAGS = [
        self::OBJECT_QUOTE => self::OBJECT_QUOTE_FLAG,
        self::OBJECT_ORDER => self::OBJECT_ORDER_FLAG
    ];

    /**
     * @var \Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue
     */
    protected $quoteFieldValueResourceModel;

    /**
     * @var \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue
     */
    protected $orderFieldValueResourceModel;

    /**
     * @var \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser
     */
    protected $checkoutFieldsParser;

    /**
     * @var array
     */
    protected $checkoutFields = [];

    /**
     * UpdateCheckoutFieldsByDate constructor.
     *
     * @param \Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue $quoteFieldValueResourceModel
     * @param \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue $orderFieldValueResourceModel
     * @param \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $checkoutFieldsParser
     */
    public function __construct(
        \Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue $quoteFieldValueResourceModel,
        \Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue $orderFieldValueResourceModel,
        \Digidirect\CheckoutFields\Helper\Xml\Fields\Parser $checkoutFieldsParser
    ) {
        $this->quoteFieldValueResourceModel = $quoteFieldValueResourceModel;
        $this->orderFieldValueResourceModel = $orderFieldValueResourceModel;
        $this->checkoutFieldsParser = $checkoutFieldsParser;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('digidirect:checkoutfields:update_order_quote_checkout_fields')
            ->setDescription('Populate field by date if provided field is not empty')
            ->setDefinition([
                new InputArgument(
                    self::CHECK_FIELD_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Check field.'
                ),
                new InputArgument(
                    self::UPDATE_FIELD_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Update field.'
                ),
                new InputArgument(
                    self::VALUE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Update by value.'
                ),
                new InputArgument(
                    self::OBJECT_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Object that will be affected.'
                )
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkField = $input->getArgument(self::CHECK_FIELD_ARGUMENT);
        if (!$this->validateField($checkField)) {
            $output->writeln((string)__('Wrong custom checkout field: %1', $checkField)->render());

            return;
        }

        $updateField = $input->getArgument(self::UPDATE_FIELD_ARGUMENT);
        if (!$this->validateField($updateField)) {
            $output->writeln((string)__('Wrong custom checkout field: %1', $updateField));

            return;
        }

        $objectFlags = 0;
        $objectValue = (string)$input->getArgument(self::OBJECT_ARGUMENT);
        if (isset(self::OBJECTS_FLAGS[$objectValue])) {
            $objectFlags |= self::OBJECTS_FLAGS[$objectValue];
        } else {
            foreach (self::OBJECTS_FLAGS as $flag) {
                $objectFlags |= $flag;
            }
        }

        $stringValue = (string)$input->getArgument(self::VALUE_ARGUMENT);

        if ($objectFlags & self::OBJECT_QUOTE_FLAG) {
            $quotes = $this->updateQuotes(
                $checkField,
                $updateField,
                $stringValue,
                $this->getCheckoutFields()[$updateField][AbstractType::XML_FRONTEND_NAME]
            );

            if ($quotes) {
                $output->writeln((string)__('Quote custom checkout fields were updated'));
            }
        }

        if ($objectFlags & self::OBJECT_ORDER_FLAG) {
            $orders = $this->updateOrders(
                $checkField,
                $updateField,
                $stringValue,
                $this->getCheckoutFields()[$updateField][AbstractType::XML_FRONTEND_NAME]
            );

            if ($orders) {
                $output->writeln((string)__('Order custom checkout fields were updated'));
            }
        }
    }

    /**
     * @return array
     */
    protected function getCheckoutFields(): array
    {
        if (!$this->checkoutFields) {
            $this->checkoutFields = $this->checkoutFieldsParser->getAllFields();
        }

        return $this->checkoutFields;
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function validateField(string $field): bool
    {
        $fields = $this->getCheckoutFields();

        return isset($fields[$field]);
    }

    /**
     * @param string $checkField
     * @param string $updateField
     * @param string $value
     * @param string $code
     * @return int Number of affected rows
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function updateQuotes(string $checkField, string $updateField, string $value, string $code): int
    {
        $emptyValue = serialize('');

        $connection = $this->quoteFieldValueResourceModel->getConnection();

        /**
         * SELECT check_table.quote_id
         * FROM b2b.digidirect_checkout_fields_quote_field_value AS check_table
         * LEFT JOIN b2b.digidirect_checkout_fields_quote_field_value AS update_table
         *   ON update_table.quote_id = check_table.quote_id AND update_table.field_id = '$updateField'
         * WHERE check_table.field_id = '$checkField' AND check_table.value != 's:0:"";'
         *   AND update_table.value IS NULL OR update_table.value = 's:0:"";'
         */
        $select = $connection->select()
            ->from(
                ['check_table' => $this->quoteFieldValueResourceModel->getMainTable()],
                ['check_table.' . QuoteFieldValueInterface::QUOTE_ID]
            )
            ->joinLeft(
                ['update_table' => $this->quoteFieldValueResourceModel->getMainTable()],
                $connection->quoteInto(
                    'update_table.' . QuoteFieldValueInterface::QUOTE_ID
                    . ' = check_table.' . QuoteFieldValueInterface::QUOTE_ID
                    . ' AND update_table.' . QuoteFieldValueInterface::FIELD_ID . ' = ?',
                    $updateField
                ),
                []
            )
            ->where('check_table.' . QuoteFieldValueInterface::FIELD_ID . ' = ?', $checkField)
            ->where('check_table.' . QuoteFieldValueInterface::VALUE . ' != ?', $emptyValue)
            ->where(
                'update_table.' . QuoteFieldValueInterface::VALUE . ' IS NULL'
                . ' OR update_table.' . QuoteFieldValueInterface::VALUE . ' = ?',
                $emptyValue
            );
        $quoteIds = $connection->fetchCol($select);
        if (!$quoteIds) {
            return 0;
        }

        $values = [];
        foreach ($quoteIds as $quoteId) {
            $values[] = [
                QuoteFieldValueInterface::CODE => $code,
                QuoteFieldValueInterface::QUOTE_ID => $quoteId,
                QuoteFieldValueInterface::VALUE => serialize($value),
                QuoteFieldValueInterface::FIELD_ID => $updateField,
            ];
        }

        return $connection->insertOnDuplicate(
            $this->quoteFieldValueResourceModel->getMainTable(),
            $values,
            [QuoteFieldValueInterface::VALUE]
        );
    }

    /**
     * @param string $checkField
     * @param string $updateField
     * @param string $value
     * @param string $code
     * @return int Number of affected rows
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function updateOrders(string $checkField, string $updateField, string $value, string $code): int
    {
        $emptyValue = serialize('');

        $connection = $this->orderFieldValueResourceModel->getConnection();

        /**
         * SELECT check_table.order_id
         * FROM b2b.digidirect_checkout_fields_order_field_value AS check_table
         * LEFT JOIN b2b.digidirect_checkout_fields_order_field_value AS update_table
         *   ON update_table.order_id = check_table.order_id AND update_table.field_id = '$updateField'
         * WHERE check_table.field_id = '$checkField' AND check_table.value != 's:0:"";'
         *   AND update_table.value IS NULL OR update_table.value = 's:0:"";'
         */
        $select = $connection->select()
            ->from(
                ['check_table' => $this->orderFieldValueResourceModel->getMainTable()],
                ['check_table.' . OrderFieldValueInterface::ORDER_ID]
            )
            ->joinLeft(
                ['update_table' => $this->orderFieldValueResourceModel->getMainTable()],
                $connection->quoteInto(
                    'update_table.' . OrderFieldValueInterface::ORDER_ID
                    . ' = check_table.' . OrderFieldValueInterface::ORDER_ID
                    . ' AND update_table.' . OrderFieldValueInterface::FIELD_ID . ' = ?',
                    $updateField
                ),
                []
            )
            ->where('check_table.' . OrderFieldValueInterface::FIELD_ID . ' = ?', $checkField)
            ->where('check_table.' . OrderFieldValueInterface::VALUE . ' != ?', $emptyValue)
            ->where(
                'update_table.' . OrderFieldValueInterface::VALUE . ' IS NULL'
                . ' OR update_table.' . OrderFieldValueInterface::VALUE . ' = ?',
                $emptyValue
            );
        $orderIds = $connection->fetchCol($select);
        if (!$orderIds) {
            return 0;
        }

        $values = [];
        foreach ($orderIds as $orderId) {
            $values[] = [
                OrderFieldValueInterface::CODE => $code,
                OrderFieldValueInterface::ORDER_ID => $orderId,
                OrderFieldValueInterface::VALUE => serialize($value),
                OrderFieldValueInterface::FIELD_ID => $updateField,
            ];
        }

        return $connection->insertOnDuplicate(
            $this->orderFieldValueResourceModel->getMainTable(),
            $values,
            [QuoteFieldValueInterface::VALUE]
        );
    }
}
