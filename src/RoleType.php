<?php

namespace JobMetric\Rolix;

use Illuminate\Support\Traits\Macroable;
use JobMetric\Media\Typeify\HasMediaType;
use JobMetric\Metadata\Typeify\HasMetadataType;
use JobMetric\Translation\Typeify\HasTranslationType;
use JobMetric\Typeify\BaseType;
use JobMetric\Typeify\Traits\HasHierarchicalType;
use JobMetric\Typeify\Traits\List\ShowDescriptionInListType;
use JobMetric\Typeify\Traits\List\RemoveFilterInListType;

class RoleType extends BaseType
{
    use Macroable,
        HasHierarchicalType,
        HasTranslationType,
        HasMetadataType,
        HasMediaType,
        HasHierarchicalType,
        ShowDescriptionInListType,
        RemoveFilterInListType;

    protected function typeName(): string
    {
        return 'role-type';
    }
}
