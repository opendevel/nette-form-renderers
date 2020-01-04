<?php declare(strict_types = 1);

namespace Opendevel\Nette\Forms\Rendering;

class Bootstrap4DefaultFormRenderer extends Bootstrap4BasicFormRenderer
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
            'container' => 'div class="form-group"',
            '.required' => 'required',
            '.optional' => null,
            '.odd' => null,
            '.error' => 'has-error',
        ],

        'control' => [
            'container' => null,
            '.odd' => null,

            'description' => 'span class="help-block text-muted"',
            'requiredsuffix' => '',
            'errorcontainer' => 'span class="help-block text-danger"',
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
            'class' => null,
            '.required' => 'required',
        ],

        'hidden' => [
            'container' => 'div',
        ],
    ];

}
