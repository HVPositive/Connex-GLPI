<?php
class PluginFormcreatorRadiosField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

         $values = $this->getAvailableValues();
         if (!empty($values)) {
            echo '<div class="formcreator_radios">';
            $i = 0;
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $checked = ($this->getValue() == $value) ? ' checked' : '';
                  echo '<input type="radio" class="form-control"
                        name="formcreator_field_' . $this->fields['id'] . '"
                        id="formcreator_field_' . $this->fields['id'] . '_' . $i . '"
                        value="' . addslashes($value) . '"' . $checked . ' /> ';
                  echo '<label for="formcreator_field_' . $this->fields['id'] . '_' . $i . '">';
                  echo $value;
                  echo '</label>';
               }
            }
            echo '</div>';
         }
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("input[name=\'formcreator_field_' . $this->fields['id'] . '\']").on("change", function() {
                        jQuery("input[name=\'formcreator_field_' . $this->fields['id'] . '\']").each(function() {
                           if (this.checked == true) {
                              formcreatorChangeValueOf (' . $this->fields['id'] . ', this.value);
                           }
                        });
                     });
                  });
               </script>';

      } else {
         echo $this->getAnswer();
      }
   }

   public static function getName() {
      return __('Radios', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         if (empty($input['values'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         } else {
            // trim values
            $input['values'] = $this->trimValue($input['values']);
            $input['values'] = addslashes($input['values']);
         }
      }
      if (isset($input['default_values'])) {
         // trim values
         $input['default_values'] = $this->trimValue($input['default_values']);
         $input['default_values'] = addslashes($input['default_values']);
      }
      return $input;
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0, 'prices' => 0,
      ];
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         if (static::IS_MULTIPLE) {
            return explode("\r\n", $this->fields['default_values']);
         }
         return $this->fields['default_values'];
      }
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['radios'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
