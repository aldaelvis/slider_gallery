<?php

namespace Drupal\slider_gallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

/**
 * Plugin implementation of the 'image_slider_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "image_slider_formatter",
 *   label = @Translation("Slider de Imágenes"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageSliderFormatter extends FormatterBase
{

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings()
  {
    return array_merge(parent::defaultSettings(), [
      'items_to_show' => 5,  // Valor predeterminado: mostrar 5 imágenes
      'link_behavior' => 'none',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state)
  {
    $elements = parent::settingsForm($form, $form_state);

    // Campo para seleccionar la cantidad de imágenes a mostrar
    $elements['items_to_show'] = [
      '#type' => 'number',
      '#title' => $this->t('Cantidad de imágenes a mostrar'),
      '#default_value' => $this->getSetting('items_to_show'),
      '#min' => 1,
      '#description' => $this->t('Define la cantidad de imágenes que se mostrarán en el slider.'),
    ];

    $elements['link_behavior'] = [
      '#type' => 'select',
      '#title' => $this->t('Link image to'),
      '#default_value' => $this->getSetting('link_behavior'),
      '#options' => [
        'none' => $this->t('Nothing'),
        'content' => $this->t('Content'),
        'file' => $this->t('File'),
      ],
      '#description' => $this->t('Define the link behavior for the slider.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary()
  {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Mostrar @items imágenes', ['@items' => $this->getSetting('items_to_show')]);
    $link_behavior = $this->getSetting('link_behavior');

    $summary[] = match ($link_behavior) {
      'file' => $this->t('Las imágenes enlazan al archivo.'),
      'content' => $this->t('Las imágenes enlazan al contenido.'),
      default => $this->t('Las imágenes no tienen enlace.'),
    };
    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode)
  {
    $images = [];
    $items_to_show = $this->getSetting('items_to_show');
    $link_behavior = $this->getSetting('link_behavior');
    foreach ($items as $index => $item) {
      if ($index >= $items_to_show) {
        break;  // Detener el ciclo si ya hemos mostrado la cantidad configurada de imágenes
      }

      $file = $item->entity;
      if ($file) {
        $imageUrl = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        $linkUrl = null;

        switch ($link_behavior) {
          case 'file':
            $linkUrl = $imageUrl;
            break;
          case 'content':
            $parent = $item->getEntity();
            if ($parent instanceof NodeInterface) {
              $linkUrl = $parent->toUrl()->toString();
            }
            break;
        }

        $images[] = [
          'image_url' => $imageUrl,
          'link_url' => $linkUrl,
        ];
      }
    }

    // Construimos el render array para usar la plantilla Twig.
    $elements = [
      '#theme' => 'image_slider',
      '#images' => $images,
      // Usamos un identificador único para diferenciar instancias (por ejemplo, el field name y delta).
      '#slider_id' => md5(implode('-', $images)),
      '#attached' => [
        'library' => [
          'slider_gallery/slider',
        ],
      ],
    ];

    return [$elements];
  }
}
