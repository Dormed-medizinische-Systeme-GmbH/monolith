<script lang="ts">
    import { Toaster as SonnerPrimitive, type ToasterProps } from 'svelte-sonner';

    let { ...restProps }: ToasterProps = $props();

</script>

<SonnerPrimitive
    theme="light"
    class="toaster group"
    position="bottom-right"
    style="--normal-bg: var(--popover); --normal-text: var(--popover-foreground); --normal-border: var(--border);"
    {...restProps}
/>
