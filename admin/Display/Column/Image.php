<?php

namespace Admin\Display\Column;

use SleepingOwl\Admin\Display\Column\NamedColumn;

class Image extends NamedColumn
{
    /**
     * @var string
     */
    protected $imageWidth = '80px';

    /**
     * @var string
     */
    protected $view = 'admin::column.smartImage';

    /**
     * @return string
     */
    public function getImageWidth()
    {
        return $this->imageWidth;
    }

    /**
     * @param string $width
     *
     * @return $this
     */
    public function setImageWidth($width)
    {
        $this->imageWidth = $width;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $value = $this->getModelValue();
        if ( ! empty($value) && (strpos($value, '://') === false)) {
            $value = asset(strpos($value, 'uploads/images') === false ? config('sleeping_owl.imagesUploadDirectory') . '/' . $value : $value);
        }

        return parent::toArray() + [
                'value'      => $value,
                'imageWidth' => $this->getImageWidth(),
                'showTags'   => $this->getShowTags(),
            ];
    }
}
