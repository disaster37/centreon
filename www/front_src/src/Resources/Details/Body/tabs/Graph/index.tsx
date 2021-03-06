import * as React from 'react';

import { pick, map } from 'ramda';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import { ResourceEndpoints } from '../../../../models';
import { TimePeriodId, timePeriods, getTimePeriodById } from './models';
import PerformanceGraph from '../../../../Graph/Performance';
import StatusGraph from '../../../../Graph/Status';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  header: {
    padding: theme.spacing(2),
  },
  periodSelect: {
    width: 250,
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(4),
    gridTemplateRows: '250px 100px',
  },
  graph: {
    margin: 'auto',
    height: '100%',
  },
  performance: {
    width: '100%',
  },
  status: {
    marginTop: theme.spacing(2),
    width: '90%',
  },
}));

const timePeriodSelectOptions = map(pick(['id', 'name']), timePeriods);

interface Props {
  endpoints: Pick<ResourceEndpoints, 'statusGraph' | 'performanceGraph'>;
}

const GraphTab = ({ endpoints }: Props): JSX.Element => {
  const classes = useStyles();

  const {
    statusGraph: statusGraphEndpoint,
    performanceGraph: performanceGraphEndpoint,
  } = endpoints;

  const [selectedTimePeriodId, setSelectedTimePeriodId] = React.useState<
    TimePeriodId
  >('last_24_h');

  const getQueryParams = (timePeriodId): string => {
    const selectedTimePeriod = getTimePeriodById(timePeriodId);

    const now = new Date(Date.now()).toISOString();
    const start = selectedTimePeriod.getStart().toISOString();

    return `?start=${start}&end=${now}`;
  };

  const [periodQueryParams, setPeriodQueryParams] = React.useState(
    getQueryParams(selectedTimePeriodId),
  );

  const changeSelectedPeriod = (event): void => {
    setSelectedTimePeriodId(event.target.value);
    const queryParamsForSelectedPeriodId = getQueryParams(event.target.value);
    setPeriodQueryParams(queryParamsForSelectedPeriodId);
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={timePeriodSelectOptions}
          selectedOptionId={selectedTimePeriodId}
          onChange={changeSelectedPeriod}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <PerformanceGraph
            endpoint={`${performanceGraphEndpoint}${periodQueryParams}`}
          />
        </div>
        <div className={`${classes.graph} ${classes.status}`}>
          <StatusGraph
            endpoint={`${statusGraphEndpoint}${periodQueryParams}`}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
