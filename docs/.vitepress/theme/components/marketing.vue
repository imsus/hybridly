<script setup lang="ts">
interface Feature {
	title: string
	description: string
	url?: string
	icon?: string
}

const features: Feature[] = [
	{
		icon: 'i-mdi:language-typescript',
		title: 'TypeScript ready',
		description: "If it's typable, it's typed. Hybridly supports typing paginators, routes, authorizations and global properties.",
		url: '/guide/typescript',
	},
	{
		icon: 'i-mdi:dock-window',
		title: 'Dialogs',
		description: 'Effortlessly implement URL-based dialogs, just like you would implement a page component.',
		url: '/guide/dialogs',
	},
	{
		icon: 'i-mdi:lock-outline',
		title: 'Authorization',
		description: 'Hybridly extends <a href="https://github.com/spatie/laravel-data">data objects</a> to provide an interface for generating typings for authorizations.',
		url: '/guide/authorization',
	},
	{
		icon: 'i-mdi:web',
		title: 'Typed routes',
		description: 'Define your routes server-side like you\'re used to, Hybridly takes care of generating types for them.',
		url: '/guide/routing',
	},
	{
		icon: 'i-heroicons:funnel',
		title: 'Refining',
		description: 'Filter and sort queries using the built-in refinement tools and build your UI around the provided composable.',
		url: '/guide/refining',
	},
	{
		icon: 'i-mdi:table-large',
		title: 'Tables',
		description: 'Build datatables with full control over their appearance, including inline and bulk actions, filtering, sorting and more.',
		url: '/guide/tables',
	},
	{
		icon: 'i-mdi:shape',
		title: 'Flexible architecture',
		description: "Whether you prefer Laravel's default architecture, modules, slices, or something custom, Hybridly supports it.",
		url: '/guide/architecture',
	},
	{
		icon: 'i-mdi:microsoft-visual-studio-code',
		title: 'Visual Studio Code',
		description: 'Auto-complete component names, and navigate between controllers, layouts and views with a single click.',
		url: '/guide/visual-studio-code',
	},
	{
		icon: 'i-mdi:vuejs',
		title: 'Vue DevTools',
		description: 'Integrates with Vue DevTools for painless property inspection and debugging.',
		url: '/guide/devtools',
	},
	// {
	// 	icon: 'i-mdi:code-braces',
	// 	title: 'Made for developers',
	// 	description: 'Hybridly is packed with quality-of-life features that should drastically improve your developer experience.',
	// 	url: '/guide/comparison-with-inertia',
	// },
]
</script>

<template>
	<client-only>
		<section class="relative mx-auto mt-20 flex size-full max-w-5xl flex-col md:px-10 lg:mt-40 xl:px-0">
			<!-- Hero -->
			<div class="flex w-full flex-col items-center justify-between gap-x-40 px-6 lg:flex-row lg:items-start lg:px-0">
				<!-- Left block -->
				<section class="group max-w-2xl grow [&>*]:transition [&>*]:duration-500 [&>*]:ease-in-out">
					<!-- Title -->
					<h1 class="title font-title title-bg bg-clip-text text-center text-5xl font-medium uppercase text-transparent lg:text-left lg:text-8xl lg:group-hover:-translate-x-12">
						Hybridly
					</h1>
					<!-- Subtitle -->
					<p class="mt-2 text-center text-2xl font-medium lg:text-left lg:text-3xl lg:group-hover:-translate-x-8">
						Server-driven, client-rendered applications.
					</p>
					<!-- Description -->
					<p class="mt-4 text-center text-lg font-medium opacity-60 lg:text-left lg:text-2xl lg:group-hover:-translate-x-4">
						Build single-page applications the classic way. No API, no route duplication, no front-end state. Fully server-driven.
					</p>
					<!-- Actions -->
					<div class="mt-10 flex flex-wrap justify-center gap-4 lg:justify-start">
						<a class="px-4.5 group flex items-center rounded-full bg-[#FFEAEF] py-2.5 font-semibold text-[#DB7DA8] transition hover:bg-[#FFDBE3] hover:text-[#D6689B] dark:bg-[--vp-c-brand-light] dark:text-[--vp-c-bg] dark:hover:bg-[--vp-c-brand-lighter] dark:hover:text-[--vp-c-bg]" href="/guide/">
							Get started
							<svg
								class="ml-2 transition group-hover:translate-x-1"
								xmlns="http://www.w3.org/2000/svg"
								width="10"
								height="10"
								viewBox="0 0 24 24"
							>
								<path
									d="M13.025 1l-2.847 2.828 6.176 6.176h-16.354v3.992h16.354l-6.176 6.176 2.847 2.828 10.975-11z"
									fill="currentcolor"
								/>
							</svg>
						</a>
						<!-- <a class="px-4.5 group flex items-center rounded-full border border-[--vp-button-alt-border] bg-[--vp-c-bg-soft] py-2 font-medium text-[--vp-c-text-1] transition hover:bg-[--vp-c-bg-mute]" href="/guide/demonstration">Demonstration</a> -->
						<a class="px-4.5 group flex items-center rounded-full border border-[--vp-button-alt-border] bg-[--vp-c-bg-soft] py-2 font-medium text-[--vp-c-text-1] transition hover:bg-[--vp-c-bg-mute]" href="https://github.com/hybridly/hybridly">See on GitHub</a>
					</div>
				</section>

				<!-- Logo block -->
				<div class="lg:h-55 lg:w-55 h-30 w-30 group relative order-first mb-20 flex shrink-0 items-center justify-center transition duration-300 hover:scale-105 lg:order-last lg:mr-20 lg:mt-20">
					<img class="group-hover:scale-85 absolute z-[1] size-full transition duration-300" src="/logo.svg" />
					<div class="image-bg absolute -inset-5 rotate-45 opacity-40 dark:opacity-70" />
				</div>
			</div>

			<!-- Features -->
			<section class="mt-24 grid grid-cols-1 gap-8 px-8 sm:grid-cols-2 lg:grid-cols-3 lg:px-0">
				<template v-for="feature in features" :key="feature.title">
					<component
						:is="feature.url ? 'a' : 'div'"
						class="group relative flex flex-col justify-center gap-4 rounded-lg bg-[--vp-c-bg-soft] p-6 ring-2 ring-transparent hover:ring-[--vp-c-brand]"
						:class="{ 'cursor-pointer ': feature.url }"
						:href="feature.url"
					>
						<div class="h-18 w-18 absolute right-2 top-2 flex shrink-0 items-center justify-center rounded p-2">
							<div :class="feature.icon" class="size-full text-[--vp-c-text-2] opacity-0 transition duration-300 group-hover:opacity-5 dark:group-hover:opacity-5" />
						</div>
						<div>
							<span class="inline-block font-semibold transition duration-300 group-hover:text-[--vp-c-text-2]" v-text="feature.title" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<p class="mt-2 text-sm font-medium leading-relaxed text-[--vp-c-text-2] transition duration-300 group-hover:translate-x-1 group-hover:text-[--vp-c-text-1]" v-html="feature.description" />
						</div>
					</component>
				</template>
			</section>
		</section>
	</client-only>
</template>

<style scoped>
.title-bg {
	animation: title-gradient 20s ease infinite;
	background-size: 400% 400%;
  background-image: linear-gradient(
		-45deg,
		var(--vp-logo-shadow-1),
		var(--vp-logo-shadow-2),
		var(--vp-logo-shadow-3),
		var(--vp-logo-shadow-4),
		var(--vp-logo-shadow-2),
		var(--vp-logo-shadow-4),
		var(--vp-logo-shadow-3),
		var(--vp-logo-shadow-2)
	);
}

@keyframes title-gradient {
	0% {
		background-position: 0% 50%;
	}
	50% {
		background-position: 100% 50%;
	}
	100% {
		background-position: 0% 50%;
	}
}

.image-bg {
  filter: blur(40px);
	animation: image-gradient 10s ease infinite;
	background-size: 400% 400%;
  background-image: linear-gradient(
		-45deg,
		var(--vp-logo-shadow-1),
		var(--vp-logo-shadow-2),
		var(--vp-logo-shadow-3),
		var(--vp-logo-shadow-4),
		var(--vp-logo-shadow-2),
		var(--vp-logo-shadow-4),
		var(--vp-logo-shadow-3),
		var(--vp-logo-shadow-2)
	);
}

@keyframes image-gradient {
	0% {
		background-position: 0% 50%;
		border-radius: 75px;
		transform: rotate(0deg);
	}
	50% {
		background-position: 100% 50%;
		border-radius: 150px;
		transform: rotate(100deg);
	}
	100% {
		background-position: 0% 50%;
		border-radius: 75px;
		transform: rotate(00deg);
	}
}

.title::selection {
	background-color: rgba(244, 114, 182, 0.2);
	color: white;
}
</style>
