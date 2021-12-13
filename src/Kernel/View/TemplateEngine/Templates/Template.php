<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Templates;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\View\InheritException;
use Fwt\Framework\Kernel\Exceptions\View\TemplateNotFoundException;
use Fwt\Framework\Kernel\Exceptions\View\UnknownArgumentException;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateFactory;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRegexBuilder;

class Template
{
    protected string $path;
    protected string $template;
    protected string $content;
    /**
     * @var Template[] $includes
     */
    protected array $includes = [];
    protected ?self $parent = null;
    protected TemplateFactory $factory;
    /**
     * @var Block[] $blocks
     */
    protected array $blocks;
    protected array $args;

    public function __construct(string $template, array $args = [])
    {
        $this->factory = new TemplateFactory();
        $this->template = $template;
        $this->args = $args;
        $this->setPath(App::$app->getProjectDir() . '/templates/' . $template);

        $this->loadContent();
        $this->initIncludes();
        $this->initInherits();
    }

    public function include(self $template): self
    {
        $this->includes[$template->getTemplate()] = $template;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function inherit(self $template, array $blocks): self
    {
        $this->parent = $template;
        $this->blocks = $blocks;
        $this->parent->blocks = $blocks;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function renderArgs(): void
    {
        $regex = TemplateRegexBuilder::getRegexForVars();

        $this->setContent(preg_replace_callback($regex,
            function ($matches) {
                if (!array_key_exists($matches[1], $this->getArgs())) {
                    throw new UnknownArgumentException($matches[0]);
                }

                return $this->getArgs()[$matches[1]];
            }, $this->content));
    }

    public function renderIncludes(): void
    {
        $include = TemplateRegexBuilder::getBuilder()
            ->setParentheses()
            ->name(TemplateRegexBuilder::INCLUDE)
            ->getRegex();

        $this->setContent(preg_replace_callback($include,
            function ($matches) {
                return $this->getIncludes()[$matches[1]]->getContent();
            }, $this->content));
    }

    public function renderBlocks(): self
    {
        $regexBuilder = TemplateRegexBuilder::getBuilder()
            ->name(TemplateRegexBuilder::CONTENT)
            ->useNumbers()
            ->setParentheses();

        foreach ($this->blocks as $block) {
            $regexBuilder->setContent($block->getName());

            preg_match_all($regexBuilder->getRegex(),
                $this->content, $content, PREG_OFFSET_CAPTURE
            );

            if (count($content) !== 1) {
                throw InheritException::invalidContentBlocksCount($block->getName(), count($content));
            }

            $this->setContent(preg_replace($regexBuilder->getRegex(), $block->getContent(), $this->content));
        }

        $this->removeExtraBlocks();

        return $this;
    }

    protected function removeExtraBlocks(): self
    {
        $regexBuilder = TemplateRegexBuilder::getBuilder()
            ->useNumbers()
            ->name(TemplateRegexBuilder::CONTENT)
            ->setParentheses();

        $this->setContent(preg_replace($regexBuilder->getRegex(), '', $this->content));

        return $this;
    }

    protected function loadContent(): void
    {
        $this->content = file_get_contents($this->path);
    }

    protected function initIncludes(): void
    {
        $include = TemplateRegexBuilder::getBuilder()
            ->name(TemplateRegexBuilder::INCLUDE)
            ->setParentheses()
            ->getRegex();

        preg_match_all($include,
            $this->content, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $this->include($this->factory->create($match[1], $this->args));
        }
    }

    protected function initInherits(): void
    {
        $regexBuilder = TemplateRegexBuilder::getBuilder()
            ->name(TemplateRegexBuilder::INHERIT)
            ->setParentheses();

        preg_match_all($regexBuilder->getRegex(),
            $this->content, $inherits, PREG_SET_ORDER
        );

        if (count($inherits) > 1) {
            throw InheritException::inheritMoreThanOnce($this);
        } elseif (count($inherits) === 0) {
            $this->parent = null;

            return;
        }

        $parent = new self($inherits[0][1], $this->args);

        $regexBuilder->name(TemplateRegexBuilder::BLOCK)->useNumbers();

        preg_match_all($regexBuilder->getRegex(),
            $this->content, $blocks, PREG_OFFSET_CAPTURE + PREG_SET_ORDER
        );

        $regexBuilder->name(TemplateRegexBuilder::ENDBLOCK)
            ->useNumbers(false)
            ->setParentheses(false);

        preg_match_all($regexBuilder->getRegex(),
            $this->content, $endBlocks, PREG_OFFSET_CAPTURE + PREG_SET_ORDER
        );

        if (count($blocks) !== count($endBlocks)) {
            throw InheritException::invalidEndBlocksCount();
        }

        $contentBlocks = [];

        foreach ($blocks as $num => $block) {
            $open = $block[0][1] + strlen($block[0][0]);
            $close = $endBlocks[$num][0][1];
            $length = $close - $open;
            $name = $block[1][0];

            $content = substr($this->content, $open, $length);

            $contentBlocks[] = new Block($name, $content);
        }

        $this->inherit($parent, $contentBlocks);
    }

    protected function setPath(string $path): void
    {
        if (file_exists($path)) {
            $this->path = $path;
        } else {
            throw new TemplateNotFoundException($path);
        }
    }

    protected function setContent($content): void
    {
        if (!is_string($content)) {
            throw new \Exception('Something went wrong');
        }

        $this->content = $content;
    }
}
