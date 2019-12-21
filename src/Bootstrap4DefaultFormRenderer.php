<?php declare(strict_types = 1);

namespace Opendevel\Nette\Forms\Rendering;

use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\IFormRenderer;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nette\Utils\IHtmlString;
use Nette\Utils\Strings;

class Bootstrap4DefaultFormRenderer implements IFormRenderer
{

    use SmartObject;

    /**
     *  /--- form.container
     *
     *    /--- error.container
     *      .... error.item [.class]
     *    \---
     *
     *    /--- hidden.container
     *      .... HIDDEN CONTROLS
     *    \---
     *
     *    /--- group.container
     *      .... group.label
     *      .... group.description
     *
     *      /--- controls.container
     *
     *        /--- pair.container [.required .optional .odd]
     *
     *          /--- label.container
     *            .... LABEL
     *            .... label.suffix
     *            .... label.requiredsuffix
     *          \---
     *
     *          /--- control.container [.odd]
     *            .... CONTROL [.required .error .text .password .file .submit .button]
     *            .... control.requiredsuffix
     *            .... control.description
     *            .... control.errorcontainer + control.erroritem
     *          \---
     *        \---
     *      \---
     *    \---
     *  \--
     * @var string[][] of HTML tags
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

            '.required' => 'required',
            '.error' => null,
            '.text' => 'form-control',
            '.password' => 'form-control',
            '.file' => 'text',
            '.email' => 'form-control',
            '.number' => 'form-control',
            '.submit' => 'btn btn-primary',
            '.image' => 'imagebutton',
            '.button' => 'btn btn-primary',
        ],

        'label' => [
            'container' => null,
            'suffix' => ':',
            'requiredsuffix' => '*',
        ],

        'hidden' => [
            'container' => 'div',
        ],
    ];

    /**
     * @var \Nette\Forms\Form
     */
    protected $form;

    /**
     * @var int
     */
    protected $counter;

    /**
     * Provides complete form rendering.
     * @param \Nette\Forms\Form $form
     * @param string|null $mode 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
     * @return string
     */
    public function render(Form $form, ?string $mode = null): string
    {
        if ($this->form !== $form) {
            $this->form = $form;
        }

        $s = '';
        if (!$mode || $mode === 'begin') {
            $s .= $this->renderBegin();
        }
        if (!$mode || strtolower($mode) === 'ownerrors') {
            $s .= $this->renderErrors();
        } elseif ($mode === 'errors') {
            $s .= $this->renderErrors(null, false);
        }
        if (!$mode || $mode === 'body') {
            $s .= $this->renderBody();
        }
        if (!$mode || $mode === 'end') {
            $s .= $this->renderEnd();
        }
        return $s;
    }

    /**
     * Renders form begin.
     */
    public function renderBegin(): string
    {
        $this->counter = 0;

        foreach ($this->form->getControls() as $control) {
            $control->setOption('rendered', false);
        }

        if ($this->form->isMethod('get')) {
            $el = clone $this->form->getElementPrototype();
            $el->action = (string)$el->action;
            $query = parse_url($el->action, PHP_URL_QUERY) ?: '';
            $el->action = str_replace("?$query", '', $el->action);
            $s = '';
            foreach (preg_split('#[;&]#', $query, -1, PREG_SPLIT_NO_EMPTY) as $param) {
                $parts = explode('=', $param, 2);
                $name = urldecode($parts[0]);
                $prefix = explode('[', $name, 2)[0];
                if (!isset($this->form[$prefix])) {
                    $s .= Html::el('input', ['type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])]);
                }
            }
            return $el->startTag() . ($s ? "\n\t" . $this->getWrapper('hidden container')->setHtml($s) : '');
        } else {
            return $this->form->getElementPrototype()->startTag();
        }
    }

    /**
     * Renders form end.
     */
    public function renderEnd(): string
    {
        $s = '';
        foreach ($this->form->getControls() as $control) {
            if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
                $s .= $control->getControl();
            }
        }
        if (iterator_count($this->form->getComponents(true, TextInput::class)) < 2) {
            $s .= '<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';
        }
        if ($s) {
            $s = $this->getWrapper('hidden container')->setHtml($s) . "\n";
        }

        return $s . $this->form->getElementPrototype()->endTag() . "\n";
    }

    /**
     * Renders validation errors (per form or per control).
     *
     * @param \Nette\Forms\IControl|null $control
     * @param bool $own
     * @return string
     */
    public function renderErrors(?IControl $control = null, bool $own = true): string
    {
        $errors = $control
            ? $control->getErrors()
            : ($own ? $this->form->getOwnErrors() : $this->form->getErrors());
        if (!$errors) {
            return '';
        }
        $container = $this->getWrapper($control ? 'control errorcontainer' : 'error container');
        $item = $this->getWrapper($control ? 'control erroritem' : 'error item');

        foreach ($errors as $error) {
            $item = clone $item;
            if ($error instanceof IHtmlString) {
                $item->addHtml($error);
            } else {
                $item->setText($error);
            }
            $container->addHtml($item);
        }
        return "\n" . $container->render($control ? 1 : 0);
    }

    /**
     * Renders form body.
     */
    public function renderBody(): string
    {
        $s = $remains = '';

        $defaultContainer = $this->getWrapper('group container');
        $translator = $this->form->getTranslator();

        foreach ($this->form->getGroups() as $groupKey => $group) {
            if (!$group->getControls() || !$group->getOption('visual')) {
                continue;
            }

            $groupKey = Strings::webalize((string)$groupKey);

            $container = $group->getOption('container', $defaultContainer);
            $container = $container instanceof Html ? clone $container : Html::el($container);

            $container->id = $group->getOption('id', 'group-' . $groupKey);

            $s .= "\n" . $container->startTag();

            $text = $group->getOption('label');
            if ($text instanceof IHtmlString) {
                $s .= $this->getWrapper('group label')->addHtml($text);
            } elseif ($text !== null) { // intentionally ==
                if ($translator !== null) {
                    $text = $translator->translate($text);
                }
                $s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
            }

            $text = $group->getOption('description');
            if ($text instanceof IHtmlString) {
                $s .= $text;
            } elseif ($text !== null) { // intentionally ==
                if ($translator !== null) {
                    $text = $translator->translate($text);
                }
                $s .= $this->getWrapper('group description')->setText($text) . "\n";
            }

            $s .= $this->renderControls($group);

            $remains = $container->endTag() . "\n" . $remains;
            if (!$group->getOption('embedNext')) {
                $s .= $remains;
                $remains = '';
            }
        }

        $s .= $remains . $this->renderControls($this->form);

        $container = $this->getWrapper('form container');
        $container->setHtml($s);
        return $container->render(0);
    }

    /**
     * Renders group of controls.
     *
     * @param \Nette\Forms\Container|\Nette\Forms\ControlGroup $parent
     * @return string
     */
    public function renderControls($parent): string
    {
        if (!($parent instanceof Container || $parent instanceof ControlGroup)) {
            throw new \Nette\InvalidArgumentException(
                'Argument must be Nette\Forms\Container or Nette\Forms\ControlGroup instance.'
            );
        }

        $container = $this->getWrapper('controls container');

        $buttons = null;
        foreach ($parent->getControls() as $control) {
            if ($control->getOption('rendered')
                || $control->getOption('type') === 'hidden'
                || $control->getForm(false) !== $this->form
            ) {
                // skip
            } elseif ($control->getOption('type') === 'button') {
                $buttons[] = $control;
            } else {
                if ($buttons) {
                    $container->addHtml($this->renderPairMulti($buttons));
                    $buttons = null;
                }
                $container->addHtml($this->renderPair($control));
            }
        }

        if ($buttons) {
            $container->addHtml($this->renderPairMulti($buttons));
        }

        $s = '';
        if (count($container)) {
            $s .= "\n" . $container . "\n";
        }

        return $s;
    }

    /**
     * Renders single visual row.
     */
    public function renderPair(IControl $control): string
    {
        $pair = $this->getWrapper('pair container');
        $pair->addHtml($this->renderLabel($control));
        $pair->addHtml($this->renderControl($control));
        $pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), true);
        $pair->class($control->hasErrors() ? $this->getValue('pair .error') : null, true);
        $pair->class($control->getOption('class'), true);
        if (($this->counter + 1) % 2) {
            $pair->class($this->getValue('pair .odd'), true);
        }

        // add form-group id automatically
        if (!empty($control->name)) {
            $pair->id = 'form-group-' . $control->name;
        }

        // add form-group id manually
        if (!empty($control->getOption('id'))) {
            $pair->id = $control->getOption('id');
        }

        return $pair->render(0);
    }

    /**
     * Renders single visual row of multiple controls.
     * @param \Nette\Forms\IControl[] $controls
     * @return string
     */
    public function renderPairMulti(array $controls): string
    {
        $s = [];
        foreach ($controls as $control) {
            if (!$control instanceof IControl) {
                throw new \Nette\InvalidArgumentException('Argument must be array of Nette\Forms\IControl instances.');
            }
            $description = $control->getOption('description');
            if ($description instanceof IHtmlString) {
                $description = ' ' . $description;
            } elseif ($description !== null) { // intentionally ==
                if ($control instanceof BaseControl) {
                    $description = $control->translate($description);
                }
                $description = ' ' . $this->getWrapper('control description')->setText($description);
            } else {
                $description = '';
            }

            $control->setOption('rendered', true);
            $el = $control->getControl();
            if ($el instanceof Html) {
                if ($el->getName() === 'input') {
                    $el->class($this->getValue("control .$el->type"), true);
                }
                $el->class($this->getValue('control .error'), $control->hasErrors());
            }
            $s[] = $el . $description;
        }
        $pair = $this->getWrapper('pair container');
        $pair->addHtml($this->renderLabel($control));
        $pair->addHtml($this->getWrapper('control container')->setHtml(implode(' ', $s)));
        return $pair->render(0);
    }

    /**
     * Renders 'label' part of visual row of controls.
     */
    public function renderLabel(IControl $control): Html
    {
        $suffix = $this->getValue('label suffix') . ($control->isRequired()
                ? $this->getValue('label requiredsuffix')
                : '');
        $label = $control->getLabel();
        if ($label instanceof Html) {
            $label->addHtml($suffix);
            if ($control->isRequired()) {
                $label->class($this->getValue('control .required'), true);
            }
        } elseif ($label !== null) { // @intentionally ==
            $label .= $suffix;
        }
        return $this->getWrapper('label container')->setHtml((string)$label);
    }

    /**
     * Renders 'control' part of visual row of controls.
     */
    public function renderControl(IControl $control): Html
    {
        $body = $this->getWrapper('control container');
        if ($this->counter % 2) {
            $body->class($this->getValue('control .odd'), true);
        }

        $description = $control->getOption('description');
        if ($description instanceof IHtmlString) {
            $description = ' ' . $description;
        } elseif ($description !== null) { // intentionally ==
            if ($control instanceof BaseControl) {
                $description = $control->translate($description);
            }
            $description = ' ' . $this->getWrapper('control description')->setText($description);
        } else {
            $description = '';
        }

        if ($control->isRequired()) {
            $description = $this->getValue('control requiredsuffix') . $description;
        }

        $control->setOption('rendered', true);
        $el = $control->getControl();
        if ($el instanceof Html) {
            if ($el->getName() === 'input') {
                $el->class($this->getValue("control .$el->type"), true);
            }
            $el->class($this->getValue('control .error'), $control->hasErrors());
        }
        return $body->setHtml($el . $description . $this->renderErrors($control));
    }

    protected function getWrapper(string $name): Html
    {
        $data = $this->getValue($name);
        return $data instanceof Html ? clone $data : Html::el($data);
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getValue(string $name)
    {
        $name = explode(' ', $name);
        return $this->wrappers[$name[0]][$name[1]] ?? null;
    }

}
