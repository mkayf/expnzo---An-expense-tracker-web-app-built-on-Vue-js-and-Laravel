import {
    BanknotesIcon,
    ChartBarSquareIcon,
    Cog6ToothIcon,
    CurrencyDollarIcon,
    DocumentCurrencyDollarIcon,
    Square3Stack3DIcon,
} from "@heroicons/vue/24/outline";

const menus = [
    {
        name: 'Dashboard',
        path: '/app/dashboard',
        icon: ChartBarSquareIcon
    },
    {
        name: 'Income',
        path: '/app/income',
        icon: CurrencyDollarIcon
    },
    {
        name: 'Expenses',
        path: '/app/expenses',
        icon: BanknotesIcon
    },
    {
        name: 'Categories',
        path: '/app/categories',
        icon: Square3Stack3DIcon
    },
    {
        name: 'Reports',
        path: '/app/reports',
        icon: DocumentCurrencyDollarIcon
    },
];

export default menus;