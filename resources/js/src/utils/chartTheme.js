export function withChartTheme(options = {}, isDark) {
    if (!isDark) {
        return options;
    }

    const muted = "#9aa8a0";
    const grid = "#2a3530";
    const surface =
        getComputedStyle(document.documentElement)
            .getPropertyValue("--app-surface")
            .trim() || "#161c19";

    const merged = {
        ...options,
        theme: {
            mode: "dark",
            ...(options.theme || {}),
        },
        chart: {
            ...(options.chart || {}),
            background: "transparent",
            foreColor: muted,
        },
        tooltip: {
            ...(options.tooltip || {}),
            theme: "dark",
        },
        legend: {
            ...(options.legend || {}),
            labels: {
                ...(options.legend?.labels || {}),
                colors: muted,
            },
        },
        xaxis: {
            ...(options.xaxis || {}),
            labels: {
                ...(options.xaxis?.labels || {}),
                style: {
                    ...(options.xaxis?.labels?.style || {}),
                    colors: muted,
                },
            },
        },
        yaxis: {
            ...(options.yaxis || {}),
            labels: {
                ...(options.yaxis?.labels || {}),
                style: {
                    ...(options.yaxis?.labels?.style || {}),
                    colors: muted,
                },
            },
        },
        grid: {
            ...(options.grid || {}),
            borderColor: grid,
        },
        noData: {
            ...(options.noData || {}),
            style: {
                ...(options.noData?.style || {}),
                color: muted,
            },
        },
    };

    if (options.chart?.type === "donut") {
        merged.stroke = {
            ...(options.stroke || {}),
            width: 2,
            colors: [surface],
        };
    }

    return merged;
}
