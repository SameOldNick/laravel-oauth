<?php

namespace SameOldNick\OAuth\Handlers\Pipeline;

use Illuminate\Pipeline\Pipeline;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;

class OAuthCallbackPipeline
{
    /**
     * @var OAuthCallbackPipelineStep[]|class-string<OAuthCallbackPipelineStep>[]
     */
    protected array $steps = [];

    /**
     * Initializes the pipeline with the given context.
     */
    public function __construct(
        public readonly OAuthCallbackPipelineContext $context,
    ) {
        //
    }

    /**
     * Set the steps to be executed in the pipeline
     *
     * @param  class-string<OAuthCallbackPipelineStep>[]  $steps
     */
    public function through(array $steps): self
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * Executes the pipeline and returns the final response.
     */
    public function run(): mixed
    {
        return $this->createPipeline()
            ->send($this->context)
            ->through(array_map(fn ($stepOrClass) => function ($context, $next) use ($stepOrClass) {
                $step = is_string($stepOrClass) ? app($stepOrClass) : $stepOrClass;

                $response = $step($context);

                // If the step returns a response, we stop the pipeline and return it
                return $response ?? $next($context);
            }, $this->steps))
            ->thenReturn();
    }

    /**
     * Creates a new pipeline instance.
     */
    protected function createPipeline(): Pipeline
    {
        return app()->make(Pipeline::class);
    }
}
