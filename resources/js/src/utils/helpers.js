export const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2)
        return decodeURIComponent(parts.pop().split(";").shift());
};

export function capitalizeEachWord(sentence) {
    const words = sentence.split(" ");
    for (let i = 0; i < words.length; i++) {
        if (words[i].length > 0) {
            words[i] =
                words[i].charAt(0).toUpperCase() +
                words[i].slice(1).toLowerCase();
        }
    }
    return words.join(" ");
}

export function formatAmount(amount, currency_iso = "PK") {
    if (typeof amount !== "number") return 0;
    const value = Number(new Intl.NumberFormat("en-" + currency_iso, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount));

    // Implement more money handling logic here ASAP you VENOM

    // if(value < 1000000000000 && value > ){

    // }

    return value;
}
