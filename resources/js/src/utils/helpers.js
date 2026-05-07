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

export function formatAmount(amount = 0, currency_iso = "PK") {
    if (typeof amount !== "number") return 0;

    let value = amount;

    const amountMapping = {
        million: 1000000,
        billion: 1000000000
    };

    if(value > 1000000000000){
        return 'Tryna be smart?'
    }
    else if(value < 1000000000000 && value >= 1000000000 ){
        value = Number(amount / amountMapping['billion']).toFixed(2) + 'B'
    } else if(value < 1000000000 && value >= 1000000){
        value = Number(amount / amountMapping['million']).toFixed(2) + 'M'
    }

    if(typeof value == "number"){
        const formattedValue = new Intl.NumberFormat("en-" + currency_iso, {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        }).format(amount);
        return formattedValue;
    }

    return value;

}
