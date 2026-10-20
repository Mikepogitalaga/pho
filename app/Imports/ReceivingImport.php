<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ReceivingImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Column mappings.
     * Keys: canonical field names.
     * Values: array of normalized (lowercase, alphanumeric-only or lower-snake) aliases.
     */
    private const COLUMN_MAP = [
        // Receiving Header Fields
        'po_number'           => [
            'purchaseordersourcedocumentno', 'purchaseordersourcedocument', 'purchaseorderno',
            'purchaseorder', 'ponumber', 'pono', 'po', 'purchase_order_source_document_no',
            'purchase_order_no', 'po_number', 'source_document_no'
        ],
        'supplier_dealer'     => [
            'supplierdealer', 'supplier', 'dealer', 'suppliername', 'companyname',
            'supplier_dealer', 'supplier_name'
        ],
        'ics_ptr_ris'         => [
            'icsptrris', 'ics_ptr_ris', 'ics', 'ptr', 'ris', 'ics_ptr', 'ptr_ris'
        ],
        'document_date'       => [
            'dateptrrisics', 'dateptr', 'ptrdate', 'documentdate', 'docdate',
            'date_ptr_ris_ics', 'document_date', 'doc_date', 'date'
        ],
        'date_received'       => [
            'datereceived', 'receiveddate', 'date_received', 'received_date'
        ],
        'stock_keeping_unit'  => [
            'stockkeepingunitprogram', 'stockkeepingunit', 'program', 'sku',
            'stock_keeping_unit_program', 'stock_keeping_unit', 'stock_keeping_unitprogram'
        ],
        'program_coordinator' => [
            'programcoordinator', 'coordinator', 'program_coordinator'
        ],

        // Item Fields
        'item_code'           => [
            'productcode', 'product_code', 'itemcode', 'item_code', 'code'
        ],
        'item_description'    => [
            'itemdescription', 'item_description', 'description', 'itemname', 'item_name', 'particulars', 'name'
        ],
        'category'            => [
            'category', 'cat', 'itemcategory', 'item_category'
        ],
        'uom'                 => [
            'uom', 'unit', 'unitofmeasure', 'unit_of_measure'
        ],
        'lot_number'          => [
            'lotnumber', 'lot_number', 'lot', 'batch', 'batchnumber', 'batch_number',
            'lotbatchsrmodelno', 'lotbatchmodelno', 'lot_batch_model_no', 'modelno', 'model_no'
        ],
        'expiry_date'         => [
            'expirydate', 'expiry_date', 'expiry', 'expdate', 'exp_date',
            'expirydateestusefullife', 'usefullife', 'useful_life', 'expiry_useful_life'
        ],
        'quantity_received'   => [
            'quantityreceived', 'quantity_received', 'quantityr', 'quantity', 'qty', 'qtyreceived', 'qty_received'
        ],
        'unit_cost'           => [
            'unitcost', 'unit_cost', 'cost', 'unitprice', 'unit_price', 'price'
        ],
        'location'            => [
            'location', 'loc', 'storage', 'shelf'
        ],
        'reorder_level'       => [
            'reorderlevel', 'reorder_level', 'reorder'
        ],
    ];

    private array $rows        = [];
    private array $rowErrors   = [];

    /** @param Collection $collection */
    public function collection(Collection $collection): void
    {
        $this->rows      = [];
        $this->rowErrors = [];

        foreach ($collection as $rowIndex => $row) {
            $rowArray = $row->toArray();

            // Store both sanitized-alphanumeric and trimmed-lowercase keys for max compatibility
            $normalised = [];
            foreach ($rowArray as $heading => $value) {
                $rawHeading   = strtolower(trim((string) $heading));
                $cleanHeading = preg_replace('/[^a-z0-9]/', '', $rawHeading);

                $normalised[$rawHeading]   = $value;
                if ($cleanHeading !== '') {
                    $normalised[$cleanHeading] = $value;
                }
            }

            $mapped = $this->mapColumns($normalised);
            $errors = $this->validateRow($mapped, $rowIndex + 2); // +2: 1-based + header row

            if (!empty($errors)) {
                $this->rowErrors[] = [
                    'row'    => $rowIndex + 2,
                    'errors' => $errors,
                    'data'   => $mapped,
                ];
            }

            $this->rows[] = $mapped;
        }
    }

    // ------------------------------------------------------------------
    // Public Accessors
    // ------------------------------------------------------------------

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->rowErrors);
    }

    // ------------------------------------------------------------------
    // Private Helpers
    // ------------------------------------------------------------------

    private function mapColumns(array $normalised): array
    {
        $result = [
            'po_number'           => '',
            'supplier_dealer'     => '',
            'ics_ptr_ris'         => '',
            'document_date'       => '',
            'date_received'       => '',
            'stock_keeping_unit'  => '',
            'program_coordinator' => '',

            'item_code'           => '',
            'item_description'    => '',
            'category'            => 'DM',
            'uom'                 => '',
            'lot_number'          => '',
            'expiry_date'         => '',
            'quantity_received'   => '',
            'unit_cost'           => '',
            'location'            => '',
            'reorder_level'       => 0,
        ];

        foreach (self::COLUMN_MAP as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalised)) {
                    $val = $normalised[$alias];

                    // Date parsing for serials or standard formatted strings
                    if (in_array($field, ['expiry_date', 'document_date', 'date_received']) && !empty($val)) {
                        if (is_numeric($val) && (float) $val > 1000) {
                            try {
                                $val = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val)
                                    ->format('Y-m-d');
                            } catch (\Throwable) {
                                // leave raw value
                            }
                        } elseif (is_string($val)) {
                            $parsed = date_create($val);
                            if ($parsed) {
                                $val = $parsed->format('Y-m-d');
                            }
                        }
                    }

                    $result[$field] = is_null($val) ? '' : trim((string) $val);
                    break;
                }
            }
        }

        // Infer or normalise category
        $cat = strtoupper(trim($result['category']));
        if ($cat === 'MDL' || str_starts_with(strtoupper($result['item_code']), 'MDL-')) {
            $result['category'] = 'MDL';
        } elseif (stripos($result['category'], 'other') !== false) {
            $result['category'] = 'Other supplies';
        } else {
            $result['category'] = 'DM';
        }

        return $result;
    }

    private function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        if (empty($row['item_description'])) {
            $errors[] = 'Item Description is required.';
        }

        $qty = $row['quantity_received'];
        if ($qty === '' || $qty === null) {
            $errors[] = 'Quantity Received is required.';
        } elseif (!is_numeric($qty) || (float) $qty <= 0) {
            $errors[] = 'Quantity Received must be a positive number (got: "' . $qty . '").';
        }

        if (!empty($row['unit_cost']) && !is_numeric($row['unit_cost'])) {
            $errors[] = 'Unit Cost must be a number (got: "' . $row['unit_cost'] . '").';
        }

        if (!empty($row['reorder_level']) && (!is_numeric($row['reorder_level']) || (int) $row['reorder_level'] < 0)) {
            $errors[] = 'Reorder Level must be a non-negative integer (got: "' . $row['reorder_level'] . '").';
        }

        if (!empty($row['expiry_date'])) {
            $parsed = date_create($row['expiry_date']);
            if (!$parsed) {
                $errors[] = 'Expiry Date format is invalid (got: "' . $row['expiry_date'] . '"). Use YYYY-MM-DD.';
            }
        }

        return $errors;
    }
}
