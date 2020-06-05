<?php declare(strict_types = 1);

namespace Opendevel\Nette\Forms\Rendering;

class Bootstrap4HorizontalFormRenderer extends Bootstrap4BasicFormRenderer
{

    /**
     * @var string[][]
     */
    public $wrappers = [
        'form' => [
            'container' => null,
        ],

        'error' => [
            'container' => 'div',
            'item' => 'div class="alert alert-error alert-danger"',
        ],

        'group' => [
            'container' => 'fieldset class="card"',
            'label' => 'h4 class="card-header"',
            'description' => 'p class="fieldset-description"',
            'subcontainer' => 'div class="card-body"',
        ],

        'controls' => [
            'container' => null,
        ],

        'pair' => [
            'container' => 'div class="form-group row"',
            '.required' => 'required',
            '.optional' => null,
            '.odd' => null,
            '.error' => 'has-error',
        ],

        'control' => [
            'container' => 'div class="col-md-9 col-lg-10"',
            '.odd' => null,

            'description' => 'small class="form-text text-muted"',
            'requiredsuffix' => '',
            'errorcontainer' => 'small class="form-text text-danger"',
            'erroritem' => '',

            '.required' => 'required',  //@todo not used
            '.error' => null,
            '.text' => 'form-control',
            '.password' => 'form-control',
            '.file' => 'text',
            '.email' => 'form-control',
            '.number' => 'form-control',
            '.submit' => 'btn btn-primary',
            '.image' => 'image',
            '.button' => 'button',
            '.textarea' => 'form-control',
            '.select' => 'form-control',
        ],

        'label' => [
            'container' => null,
            'suffix' => ':',
            'requiredsuffix' => '*',
            'class' => 'col-md-3 col-lg-2 col-form-label',
            '.required' => 'required',
        ],

        'hidden' => [
            'container' => 'div',
        ],
    ];

}
